<?php

namespace Drupal\working_hours_settings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'working_hours_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('working_hours_settings.admin_settings');

    $form['mon_work_hours'] = [
      '#type' => 'range_time',
      '#title' => 'График работы в понедельник',
      '#default_value' => $config->get('mon_work_hours'),
    ];

    $form['tu_work_hours'] = [
      '#type' => 'range_time',
      '#title' => 'График работы во вторник',
      '#default_value' => $config->get('tu_work_hours'),
    ];

    $form['we_work_hours'] = [
      '#type' => 'range_time',
      '#title' => 'График работы в среду',
      '#default_value' => $config->get('we_work_hours'),
    ];

    $form['thu_work_hours'] = [
      '#type' => 'range_time',
      '#title' => 'График работы в четверг',
      '#default_value' => $config->get('thu_work_hours'),
    ];

    $form['fr_work_hours'] = [
      '#type' => 'range_time',
      '#title' => 'График работы в пятницу',
      '#default_value' => $config->get('fr_work_hours'),
    ];

    $form['sat_work_hours'] = [
      '#type' => 'range_time',
      '#title' => 'График работы в субботу',
      '#default_value' => $config->get('sat_work_hours'),
    ];

    $form['sun_work_hours'] = [
      '#type' => 'range_time',
      '#title' => 'График работы в воскресенье',
      '#default_value' => $config->get('sun_work_hours'),
    ];

    $form['exceptions_work_hours_feeldset'] = [
      '#type' => 'fieldset',
      '#title' => 'Графики работы в исключительные дни',
      '#default_value' => $config->get('exceptions_work_hours'),
    ];

    $form['exceptions_work_hours_feeldset']['exceptions_work_hours'] = [
      '#type' => 'period_range_time',
      '#title' => 'График работы в данном периоде',
      '#default_value' => $config->get('exceptions_work_hours'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mon_work_hours = json_decode($form_state->getValue('mon_work_hours'), true);
    if (($mon_work_hours['start'] && (!$mon_work_hours['end'] || !preg_match('/(\d){2}:(\d){2}/i', $mon_work_hours['end']))) || ((!$mon_work_hours['start'] || !preg_match('/(\d){2}:(\d){2}/i', $mon_work_hours['start'])) && $mon_work_hours['end'])) {
      $form_state->setErrorByName('mon_work_hours', 'Должны быть корректно заполнено оба поля на понедельник либо оба поля должны быть пустыми');
    }
    $tu_work_hours = json_decode($form_state->getValue('tu_work_hours'), true);
    if (($tu_work_hours['start'] && (!$tu_work_hours['end'] || !preg_match('/(\d){2}:(\d){2}/i', $tu_work_hours['end']))) || ((!$tu_work_hours['start'] || !preg_match('/(\d){2}:(\d){2}/i', $tu_work_hours['start'])) && $tu_work_hours['end'])) {
      $form_state->setErrorByName('tu_work_hours', 'Должны быть корректно заполнено оба поля на вторник либо оба поля должны быть пустыми');
    }
    $we_work_hours = json_decode($form_state->getValue('we_work_hours'), true);
    if (($we_work_hours['start'] && (!$we_work_hours['end'] || !preg_match('/(\d){2}:(\d){2}/i', $we_work_hours['end']))) || ((!$we_work_hours['start'] || !preg_match('/(\d){2}:(\d){2}/i', $we_work_hours['start'])) && $we_work_hours['end'])) {
      $form_state->setErrorByName('we_work_hours', 'Должны быть корректно заполнено оба поля на среду либо оба поля должны быть пустыми');
    }
    $thu_work_hours = json_decode($form_state->getValue('thu_work_hours'), true);
    if (($thu_work_hours['start'] && (!$thu_work_hours['end'] || !preg_match('/(\d){2}:(\d){2}/i', $thu_work_hours['end']))) || ((!$thu_work_hours['start'] || !preg_match('/(\d){2}:(\d){2}/i', $thu_work_hours['start'])) && $thu_work_hours['end'])) {
      $form_state->setErrorByName('thu_work_hours', 'Должны быть корректно заполнено оба поля на четверг либо оба поля должны быть пустыми');
    }
    $fr_work_hours = json_decode($form_state->getValue('fr_work_hours'), true);
    if (($fr_work_hours['start'] && (!$fr_work_hours['end'] || !preg_match('/(\d){2}:(\d){2}/i', $fr_work_hours['end']))) || ((!$fr_work_hours['start'] || !preg_match('/(\d){2}:(\d){2}/i', $fr_work_hours['start'])) && $fr_work_hours['end'])) {
      $form_state->setErrorByName('fr_work_hours', 'Должны быть корректно заполнено оба поля на пятницу либо оба поля должны быть пустыми');
    }
    $sat_work_hours = json_decode($form_state->getValue('sat_work_hours'), true);
    if (($sat_work_hours['start'] && (!$sat_work_hours['end'] || !preg_match('/(\d){2}:(\d){2}/i', $sat_work_hours['end']))) || ((!$sat_work_hours['start'] || !preg_match('/(\d){2}:(\d){2}/i', $sat_work_hours['start'])) && $sat_work_hours['end'])) {
      $form_state->setErrorByName('sat_work_hours', 'Должны быть корректно заполнено оба поля на субботу либо оба поля должны быть пустыми');
    }
    $sun_work_hours = json_decode($form_state->getValue('sun_work_hours'), true);
    if (($sun_work_hours['start'] && (!$sun_work_hours['end'] || !preg_match('/(\d){2}:(\d){2}/i', $sun_work_hours['end']))) || ((!$sun_work_hours['start'] || !preg_match('/(\d){2}:(\d){2}/i', $sun_work_hours['start'])) && $sun_work_hours['end'])) {
      $form_state->setErrorByName('sun_work_hours', 'Должны быть корректно заполнено оба поля на воскресенье либо оба поля должны быть пустыми');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('working_hours_settings.admin_settings')
      ->set('mon_work_hours', $form_state->getValue('mon_work_hours'))
      ->set('tu_work_hours', $form_state->getValue('tu_work_hours'))
      ->set('we_work_hours', $form_state->getValue('we_work_hours'))
      ->set('thu_work_hours', $form_state->getValue('thu_work_hours'))
      ->set('fr_work_hours', $form_state->getValue('fr_work_hours'))
      ->set('sat_work_hours', $form_state->getValue('sat_work_hours'))
      ->set('sun_work_hours', $form_state->getValue('sun_work_hours'))
      ->set('exceptions_work_hours', $form_state->getValue('exceptions_work_hours'))
      ->save();
  }

  protected function getEditableConfigNames(): array {
    return [
      'working_hours_settings.admin_settings'
    ];
  }
}
