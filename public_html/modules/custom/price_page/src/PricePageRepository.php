<?php

namespace Drupal\price_page;

use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Transliteration\PhpTransliteration;

/**
 * Класс предназначен для получения и компановки информации для страницы "Цены"
 *
 * @package Drupal\price_page
 */
class PricePageRepository {

  public function getData() {
    $result = [];

    // Получаем сортировку цен через админку
    $connection = \Drupal::database();
    $query = $connection->query("SELECT entity_id, weight FROM draggableviews_structure WHERE view_name = 'service_price_ordering' AND view_display = 'page_1'");
    $weightsTmp = $query->fetchAll();
    $weights = [];
    foreach($weightsTmp as $weightFromDb) {
      $weights[$weightFromDb->entity_id] = $weightFromDb->weight;
    }

    // Получаем все цены
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'service_price')
      ->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

    // Сортируем цены согласно настройкам в админке, а зетем по ценам
    usort($nodes, static function($a, $b) use($weights) {
      $a_id = $a->id();
      $b_id = $b->id();
      if (!isset($weights[$a_id], $weights[$b_id]) || (int)$weights[$a_id] === (int)$weights[$b_id]) {
        if ((int)$a->field_cena->value === (int)$b->field_cena->value) {
          return 0;
        }
        return ((int)$a->field_cena->value < (int)$b->field_cena->value) ? -1 : 1;
      }
      return ((int)$weights[$a_id] < (int)$weights[$b_id]) ? -1 : 1;
    });


    foreach ($nodes as $node) {
      $service_type_tid_list = [];
      $service_type_tids = $node->get('field_service_type');
      foreach ($service_type_tids as $service_type_tid) {
        $service_type_tid_list[] = $service_type_tid->target_id;
      }

      $service_type2_tid_list = [];
      $service_type2_tids = $node->get('field_service_type2');
      foreach ($service_type2_tids as $service_type2_tid) {
        $service_type2_tid_list[] = $service_type2_tid->target_id;
      }
      if (!$service_type_tid_list || !$service_type2_tid_list) {
        continue;
      }

      foreach ($service_type_tid_list as $service_type_tid) {
        foreach ($service_type2_tid_list as $service_type2_tid) {
          $service_type_term = Term::load($service_type_tid);
          $service_type_term_name = $service_type_term->getName();
          $service_type_term_weight = $service_type_term->getWeight();

          $service_type2_term = Term::load($service_type2_tid);
          $service_type2_term_name = $service_type2_term->getName();
          $service_type2_term_weight = $service_type2_term->getWeight();

          $translitiration = new PhpTransliteration();
          $result[$service_type_tid]['name'] = $service_type_term_name;
          $result[$service_type_tid]['weight'] = $service_type_term_weight;
          $anchor = str_replace(' ', '_', $translitiration->transliterate($service_type_term_name, 'en', '_'));
          $result[$service_type_tid]['anchor'] = $anchor;
          $result[$service_type_tid]['types'][$service_type2_tid]['name'] = $service_type2_term_name;
          $result[$service_type_tid]['types'][$service_type2_tid]['weight'] = $service_type2_term_weight;

          $node_variables = [
            'name' => $node->getTitle(),
            'description' => $node->body->value,
            'price' => $node->field_cena->value,
            'price_old' => $node->field_old_price->value,
          ];

          $price_to = $node->field_price_to->value;
          if ($price_to) {
            $node_variables['price_to'] = $price_to;
          }

          if ((bool) $node->field_price_from->value || $price_to) {
            $node_variables['price_from'] = TRUE;
          }
          $result[$service_type_tid]['types'][$service_type2_tid]['prices'][] = $node_variables;
        }
      }
    }

    // Сортируем табы согласно весу в словаре
    if (count($result) > 1) {
      usort($result, function ($a, $b) {
        return (int) $a['weight'] <=> (int) $b['weight'];
      });
    }

    // Сортируем категории в табах согласно весу в словаре
    foreach ($result as &$tabContent) {
      if (count($tabContent['types']) > 1) {
        usort($tabContent['types'], function ($a, $b) {
          return (int) $a['weight'] <=> (int) $b['weight'];
        });
      }
    }

    return $result;
  }
}