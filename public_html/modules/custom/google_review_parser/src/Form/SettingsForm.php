<?php

namespace Drupal\google_review_parser\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'google_review_parser_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('google_review_parser.adminsettings');

    $form['access_token'] = [
      '#type' => 'textarea',
      '#title' => 'access_token',
      '#default_value' => $config->get('access_token'),
    ];

    $form['refresh_token'] = [
      '#type' => 'textarea',
      '#title' => 'refresh_token',
      '#default_value' => $config->get('refresh_token'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('google_review_parser.adminsettings')
      ->set('access_token', $form_state->getValue('access_token'))
      ->set('refresh_token', $form_state->getValue('refresh_token'))
      ->save();
  }

  protected function getEditableConfigNames(): array {
    return [
      'google_review_parser.adminsettings'
    ];
  }
}
