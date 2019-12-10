<?php

namespace Drupal\services_export\Form;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Tests\field\Functional\reEnableModuleFieldTest;

class SettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'services_export_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('services_export.adminsettings');
    $service_type_exclude_saved = $config->get('service_type_exclude');

    $randomNumber = rand();
    $form['link']['#markup'] = '<a href="/services_export_file.xls?' . $randomNumber . '">Скачать XLS-файл</a>';

    $tids = [];
    if ($service_type_exclude_saved) {
      foreach ($service_type_exclude_saved as $entity_id) {
        $tids[] = $entity_id['target_id'];
      }
    }
    if ($tids) {
      $service_type_exclude_default_value = Term::loadMultiple($tids);
    }

    $form['service_type_exclude'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      '#selection_handler' => 'default',
      '#selection_settings' => [
        'target_bundles' => ['service_type'],
      ],
      '#title' => 'Категории для исключения из файла экспорта',
      '#tags' => TRUE,
      '#default_value' => $service_type_exclude_default_value ?? NULL,
      '#maxlength' => 9999
    ];

    $service_exclude_saved = $config->get('service_exclude');

    $tids = [];
    if ($service_exclude_saved) {
      foreach ($service_exclude_saved as $entity_id) {
        $tids[] = $entity_id['target_id'];
      }
    }
    if ($tids) {
      $service_exclude_default_value = Term::loadMultiple($tids);
    }

    $form['service_exclude'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_term',
      '#selection_handler' => 'default',
      '#selection_settings' => [
        'target_bundles' => ['service'],
      ],
      '#title' => 'Услуги для исключения из файла экспорта',
      '#tags' => TRUE,
      '#default_value' => $service_exclude_default_value ?? NULL,
      '#maxlength' => 9999,
      '#size' => 500
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('services_export.adminsettings')
      ->set('service_type_exclude', $form_state->getValue('service_type_exclude'))
      ->set('service_exclude', $form_state->getValue('service_exclude'))
      ->save();
  }

  protected function getEditableConfigNames(): array {
    return [
      'services_export.adminsettings'
    ];
  }
}
