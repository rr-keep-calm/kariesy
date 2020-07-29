<?php

/**
 * @file
 * Contains \Drupal\dental_formula_field\Plugin\Field\FieldFormatter\DentalFormulaFieldDefaultFormatter.
 */

namespace Drupal\dental_formula_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/** *
 * @FieldFormatter(
 *   id = "dental_formula_field_default_formatter",
 *   label = @Translation("Dental formula"),
 *   field_types = {
 *     "dental_formula_field"
 *   }
 * )
 */
class DentalFormulaFieldDefaultFormatter extends FormatterBase {

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
