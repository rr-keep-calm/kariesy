<?php

/**
 * @file
 * Contains \Drupal\schedule_field\Plugin\Field\FieldFormatter\ScheduleFieldDefaultFormatter.
 */

namespace Drupal\schedule_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/** *
 * @FieldFormatter(
 *   id = "schedule_field_default_formatter",
 *   label = @Translation("Schedule"),
 *   field_types = {
 *     "schedule_field"
 *   }
 * )
 */
class ScheduleFieldDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      # Выводим наши элементы.
      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => $item->value,
      ];
    }

    return $element;
  }

}
