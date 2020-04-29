<?php

namespace Drupal\working_hours_settings\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "range_time_widget",
 *   module = "working_hours_settings",
 *   label = @Translation("Set range time for day of week"),
 *   field_types = {
 *     "range_time_field"
 *   }
 * )
 */
class RangeTimeWidget extends WidgetBase {

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
      '#type' => 'range_time',
      '#default_value' => $value,
    ];

    return ['value' => $element];
  }

}
