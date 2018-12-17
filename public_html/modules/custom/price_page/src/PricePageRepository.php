<?php

namespace Drupal\price_page;

use Drupal\taxonomy\Entity\Term;

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

      $result[$service_type_tid]['name'] = $service_type_term_name;
      $result[$service_type_tid]['types'][$service_type2_tid]['name'] = $service_type2_term_name;

      $node_variables = [
        'name' => $node->getTitle(),
        'description' => $node->body->value,
        'price' => $node->field_cena->value,
      ];
      $result[$service_type_tid]['types'][$service_type2_tid]['prices'][] = $node_variables;
    }
    return $result;
  }
}