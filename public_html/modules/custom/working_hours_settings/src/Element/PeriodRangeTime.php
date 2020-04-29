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
 * @FormElement("period_range_time")
 */
class PeriodRangeTime extends FormElement {
  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#theme' => 'period_range_time',
      '#label' => 'График рабты в исключительные дни',
      '#description' => 'График рабты в исключительные дни',
      '#pre_render' => [
        [$class, 'prePeriodRenderRangeTime']
      ]
    ];
  }

  public static function prePeriodRenderRangeTime($element) {
    $element['#attributes']['type'] = 'hidden';
    $element['exceptions'] = json_decode($element["#value"], TRUE);
    return $element;
  }

  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== false) {
      $exceptions = [];
      $all_params = \Drupal::request()->request->all();
      foreach ($all_params as $key => $params) {
        if (strpos($key, 'exception') === false) {
          continue;
        }
        $key_parts = explode('-', $key);
        $exceptions[$key_parts[2]][$key_parts[3]] = $params;
      }
      return json_encode($exceptions);
    }
    return $element['#default_value'];
  }

}
