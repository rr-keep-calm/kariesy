<?php

/**
 * @file
 * Contains \Drupal\dental_formula_field\Plugin\Field\FieldWidget\DentalFormulaFieldInputWidget.
 */

namespace Drupal\dental_formula_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "dental_formula_field_input_widget",
 *   module = "dental_formula_field",
 *   label = @Translation("Set dental formula"),
 *   field_types = {
 *     "dental_formula_field"
 *   }
 * )
 */
class DentalFormulaFieldInputWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   *
   * В данном методе мы настраиваем форму в которой наше значение для поля будет
   * вводиться и редактироваться - это то, что видят юзеры в админке при работе
   * с данным полем.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    $element += [
      '#type' => 'dental_formula',
      '#default_value' => $value,
    ];

    return ['value' => $element];
  }

}
