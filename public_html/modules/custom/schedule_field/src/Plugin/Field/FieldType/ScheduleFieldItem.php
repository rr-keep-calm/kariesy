<?php

/**
 * @file
 * Contains Drupal\my_color_field\Plugin\Field\FieldType\MyColorFieldItem.
 */

namespace Drupal\schedule_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "schedule_field",
 *   label = @Translation("Schedule"),
 *   module = "schedule_field",
 *   description = @Translation("Set schedule for clinics"),
 *   category = @Translation("Schedule"),
 *   default_widget = "schedule_field_input_widget",
 *   default_formatter = "schedule_field_default_formatter"
 * )
 */
class ScheduleFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   *
   * Объявляем поля для таблицы где будут храниться значения нашего поля. Нам
   * хватит одного значения value типа text и размером tiny.
   *
   * @see https://www.drupal.org/node/159605
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'size' => 'medium',
          'not null' => FALSE,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   *
   * Это указывает Drupal на то, как нужно хранить значения этого поля.
   * Например integer, string или any.
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Schedule'));

    return $properties;
  }
}
