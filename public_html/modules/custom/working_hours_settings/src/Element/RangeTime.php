<?php
/**
 * @file
 * Contains \Drupal\schedule_field\Element\Schedule.
 */

namespace Drupal\working_hours_settings\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an example element.
 *
 * @FormElement("range_time")
 */
class RangeTime extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#theme' => 'range_time',
      '#label' => 'График рабты',
      '#description' => 'График рабты',
      '#pre_render' => [
        [$class, 'preRenderRangeTime'],
      ],
    ];
  }

  public static function preRenderRangeTime($element) {
    $element['decoded_value'] = json_decode($element['#value'], true);
    return $element;
  }

  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== FALSE) {
      $all_params = \Drupal::request()->request->all();
      $day_of_week = explode('_', $element["#name"])[0];
      return json_encode(['start' => $all_params[$day_of_week . '-start'], 'end' => $all_params[$day_of_week . '-end']]);
    }
    return $element['#default_value'];
  }
}
