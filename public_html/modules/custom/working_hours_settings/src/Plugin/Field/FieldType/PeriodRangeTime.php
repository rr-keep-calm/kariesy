<?php
namespace Drupal\working_hours_settings\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * @FieldType(
 *   id = "period_range_time_field",
 *   label = @Translation("Period range time"),
 *   module = "working_hours_settings",
 *   description = @Translation("Setting range between two points in time for day period"),
 *   category = @Translation("Period range time"),
 *   default_widget = "period_range_time_input_widget",
 *   default_formatter = "period_range_time_formatter"
 * )
 */
class PeriodRangeTime extends FieldItemBase {

  /**
   * {@inheritdoc}
   *
   * Объявляем поля для таблицы где будут храниться значения нашего поля.
   *
   * @see https://www.drupal.org/node/159605
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'value' => array(
          'type' => 'text',
          'size' => 'big',
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
      ->setLabel(t('Period range time'));

    return $properties;
  }
}
