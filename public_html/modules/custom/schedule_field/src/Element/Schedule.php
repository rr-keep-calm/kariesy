<?php
/**
 * @file
 * Contains \Drupal\schedule_field\Element\Schedule.
 */

namespace Drupal\schedule_field\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

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
        [$class, 'preRenderSchedule']
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

    if ($element['#value']) {
      $element['scheduleData'] = json_decode($element['#value'], true);
    }

    return $element;
  }

  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input !== false) {

      $toSave = [];

      // Формируем строку для хранения данных по графику работы
      // Получаем все данные из всех полей графика работы
      $firstStartTime = \Drupal::request()->request->get('first-start-time');
      $firstEndTime = \Drupal::request()->request->get('first-end-time');
      $moFirstSchedule = \Drupal::request()->request->get('mo_firstSchedule');
      $tuFirstSchedule = \Drupal::request()->request->get('tu_firstSchedule');
      $weFirstSchedule = \Drupal::request()->request->get('we_firstSchedule');
      $thFirstSchedule = \Drupal::request()->request->get('th_firstSchedule');
      $frFirstSchedule = \Drupal::request()->request->get('fr_firstSchedule');

      $secondStartTime = \Drupal::request()->request->get('second-start-time');
      $secondEndTime = \Drupal::request()->request->get('second-end-time');
      $moSecondSchedule = \Drupal::request()->request->get('mo_secondSchedule');
      $tuSecondSchedule = \Drupal::request()->request->get('tu_secondSchedule');
      $weSecondSchedule = \Drupal::request()->request->get('we_secondSchedule');
      $thSecondSchedule = \Drupal::request()->request->get('th_secondSchedule');
      $frSecondSchedule = \Drupal::request()->request->get('fr_secondSchedule');

      $firstStartTimeWeekends = \Drupal::request()->request->get('first-start-time-weekends');
      $firstEndTimeWeekends = \Drupal::request()->request->get('first-end-time-weekends');
      $saFirstScheduleWeekends = \Drupal::request()->request->get('sa_firstScheduleWeekends');
      $suFirstScheduleWeekends = \Drupal::request()->request->get('su_firstScheduleWeekends');

      $secondStartTimeWeekends = \Drupal::request()->request->get('second-start-time-weekends');
      $secondEndTimeWeekends = \Drupal::request()->request->get('second-end-time-weekends');
      $saSecondScheduleWeekends = \Drupal::request()->request->get('sa_secondScheduleWeekends');
      $suSecondScheduleWeekends = \Drupal::request()->request->get('su_secondScheduleWeekends');

      if ($firstStartTime && $firstEndTime) {
        $toSave['firstShift']['start'] = $firstStartTime;
        $toSave['firstShift']['end'] = $firstEndTime;
        $toSave['firstShift']['moClinic'] = $moFirstSchedule;
        $toSave['firstShift']['tuClinic'] = $tuFirstSchedule;
        $toSave['firstShift']['weClinic'] = $weFirstSchedule;
        $toSave['firstShift']['thClinic'] = $thFirstSchedule;
        $toSave['firstShift']['frClinic'] = $frFirstSchedule;
      }

      if ($secondStartTime && $secondEndTime) {
        $toSave['secondShift']['start'] = $secondStartTime;
        $toSave['secondShift']['end'] = $secondEndTime;
        $toSave['secondShift']['moClinic'] = $moSecondSchedule;
        $toSave['secondShift']['tuClinic'] = $tuSecondSchedule;
        $toSave['secondShift']['weClinic'] = $weSecondSchedule;
        $toSave['secondShift']['thClinic'] = $thSecondSchedule;
        $toSave['secondShift']['frClinic'] = $frSecondSchedule;
      }

      if ($firstStartTimeWeekends && $firstEndTimeWeekends) {
        $toSave['firstShiftWeekends']['start'] = $firstStartTimeWeekends;
        $toSave['firstShiftWeekends']['end'] = $firstEndTimeWeekends;
        $toSave['firstShiftWeekends']['saClinic'] = $saFirstScheduleWeekends;
        $toSave['firstShiftWeekends']['suClinic'] = $suFirstScheduleWeekends;
      }

      if ($secondStartTimeWeekends && $secondEndTimeWeekends) {
        $toSave['secondShiftWeekends']['start'] = $secondStartTimeWeekends;
        $toSave['secondShiftWeekends']['end'] = $secondEndTimeWeekends;
        $toSave['secondShiftWeekends']['saClinic'] = $saSecondScheduleWeekends;
        $toSave['secondShiftWeekends']['suClinic'] = $suSecondScheduleWeekends;
      }

      if ($toSave) {
        return json_encode($toSave);
      }
    }
    return $element['#default_value'];
  }

}
