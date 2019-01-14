<?php

/**
 * @file
 * Contains \Drupal\my_color_field\Plugin\Field\FieldWidget\MyColorFieldHTML5InputWidget.
 */

namespace Drupal\schedule_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldWidget(
 *   id = "schedule_field_input_widget",
 *   module = "schedule_field",
 *   label = @Translation("Set schedule for clinics"),
 *   field_types = {
 *     "schedule_field"
 *   }
 * )
 */
class ScheduleFieldInputWidget extends WidgetBase {

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
      '#type' => 'schedule',
      '#default_value' => $value,
    ];

    return ['value' => $element];
  }

}
