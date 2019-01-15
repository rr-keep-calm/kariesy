<?php
/**
 * @file
 * Contains \Drupal\schedule_field\Element\Schedule.
 */

namespace Drupal\schedule_field\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides an example element.
 *
 * @FormElement("schedule")
 */
class Schedule extends FormElement {
  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#theme' => 'schedule',
      '#label' => 'График рабты',
      '#description' => 'График рабты',
      '#pre_render' => [
        [$class, 'preRenderSchedule'],
      ]
    ];
  }

  public static function preRenderSchedule($element) {
    $element['#attributes']['type'] = 'hidden';

    // Получаем список всех клиник
    $nids = \Drupal::entityQuery('node')->condition('type','clinic')->execute();
    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    $element['clinics'] = [];
    foreach ($nodes as $node) {
      $element['clinics'][$node->id()] = $node->getTitle();
    }

    Element::setAttributes($element, ['name', 'value']);

    return $element;
  }

}
