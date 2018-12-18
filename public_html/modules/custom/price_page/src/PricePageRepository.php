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
    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'service_price')
      ->sort('field_cena')
      ->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      $service_tid = $node->field_usluga->target_id;
      $service_fields = Term::load($service_tid)->getFields();

      $service_type_tid = $service_fields['field_service_type']->target_id;
      if (!$service_type_tid) {
        continue;
      }
      $service_type_term = Term::load($service_type_tid);
      $service_type_term_name = $service_type_term->getName();

      $service_type2_tid = $service_fields['field_service_type2']->target_id;
      if (!$service_type2_tid) {
        continue;
      }
      $service_type2_term = Term::load($service_type2_tid);
      $service_type2_term_name = $service_type2_term->getName();

      $translitiration = new PhpTransliteration();
      $result[$service_type_tid]['name'] = $service_type_term_name;
      $result[$service_type_tid]['anchor'] = $translitiration->transliterate($service_type_term_name, 'en', '_');
      $result[$service_type_tid]['types'][$service_type2_tid]['name'] = $service_type2_term_name;

      $node_variables = [
        'name' => $node->getTitle(),
        'description' => $node->body->value,
        'price' => $node->field_cena->value,
      ];
      if ((bool)$node->field_price_from->value) {
        $node_variables['price'] = 'от ' . $node_variables['price'];
      }
      $result[$service_type_tid]['types'][$service_type2_tid]['prices'][] = $node_variables;
    }
    return $result;
  }
}