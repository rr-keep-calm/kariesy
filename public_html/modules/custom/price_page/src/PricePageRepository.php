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

  public function getData($service_type) {
    $result = [];

    // Получаем все цены
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'service_price')
      ->condition('status', 1)
      ->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

    // Собираем все типы услуг для отображения в меню типов услуг
    $current_service_type_tid = NULL;
    $translitiration = new PhpTransliteration();
    foreach ($nodes as $node) {
      $service_type_tids = $node->get('field_service_type');
      foreach ($service_type_tids as $service_type_tid) {
        $service_type_tid = $service_type_tid->target_id;
        if (!$service_type_tid) {
          continue;
        }
        $service_type_term = Term::load($service_type_tid);
        $result['service_type_list'][$service_type_tid]['tid'] = $service_type_tid;
        $result['service_type_list'][$service_type_tid]['name'] = $service_type_term->getName();
        $result['service_type_list'][$service_type_tid]['weight'] = $service_type_term->getWeight();
        $result['service_type_list'][$service_type_tid]['url'] = strtolower(str_replace(' ', '_', $translitiration->transliterate($result['service_type_list'][$service_type_tid]['name'], 'en', '_')));
        $result['service_type_list'][$service_type_tid]['active'] = 0;
        if ($result['service_type_list'][$service_type_tid]['url'] === $service_type) {
          $current_service_type_tid = $service_type_tid;
          $result['service_type_list'][$service_type_tid]['active'] = 1;
        }
      }
    }

    // Сортируем меню типов услуг согласно весу в словаре
    if (isset($result['service_type_list']) && count($result['service_type_list']) > 1) {
      usort($result['service_type_list'], static function ($a, $b) {
        return (int) $a['weight'] <=> (int) $b['weight'];
      });
    }

    // Если до сих пор не определились с текущей страницей прайса значит это
    // страница по умолчанию, а значит берём первый тип услуги
    if (!$current_service_type_tid) {
      $current_service_type_tid = $result['service_type_list'][0]['tid'];
      $result['service_type_list'][0]['active'] = 1;
    }

    // выбираем только те цены, которые будут отражены на этой странице и
    // получаем их данные для отображения
    $curent_page_nodes = [];
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
        if ($current_service_type_tid !== $service_type_tid) {
          continue;
        }
        $curent_page_nodes[] = $node->id();
        foreach ($service_type2_tid_list as $service_type2_tid) {
          $node_variables = [
            'id' => $node->id(),
            'name' => $node->getTitle(),
            'description' => $node->body->value,
            'price_code' => $node->field_service_code->value,
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

          $service_type2_term = Term::load($service_type2_tid);
          $service_type2_term_name = $service_type2_term->getName();
          $service_type2_term_weight = $service_type2_term->getWeight();

          $result['types'][$service_type2_tid]['prices'][$node->id()] = $node_variables;
          $result['types'][$service_type2_tid]['name'] = $service_type2_term_name;
          $result['types'][$service_type2_tid]['weight'] = $service_type2_term_weight;
        }
      }
    }
    $curent_page_nodes = implode(',', $curent_page_nodes);

    // Получаем сортировку цен через админку для отображаемых элементов
    $connection = \Drupal::database();
    $query = $connection->query("SELECT entity_id, weight FROM draggableviews_structure WHERE view_name = 'service_price_ordering' AND view_display = 'page_1' AND entity_id IN ({$curent_page_nodes})");
    $weightsTmp = $query->fetchAll();
    $weights = [];
    foreach ($weightsTmp as $weightFromDb) {
      $weights[$weightFromDb->entity_id] = $weightFromDb->weight;
    }

    // Сортируем цены согласно настройкам в админке, а зетем по ценам
    foreach ($result['types'] as &$type) {
      usort($type['prices'], static function ($a, $b) use ($weights) {
        if (!isset($weights[$a['id']], $weights[$b['id']]) || (int) $weights[$a['id']] === (int) $weights[$b['id']]) {
          if ((int) $a['price'] === (int) $b['price']) {
            return 0;
          }
          return ((int) $a['price'] < (int) $b['price']) ? -1 : 1;
        }
        return ((int) $weights[$a['id']] < (int) $weights[$b['id']]) ? -1 : 1;
      });
    }

    // Сортируем категории на ткущей странице согласно весу в словаре
    if (isset($result['types']) && count($result['types']) > 1) {
      usort($result['types'], function ($a, $b) {
        return (int) $a['weight'] <=> (int) $b['weight'];
      });
    }

    return $result;
  }
}
