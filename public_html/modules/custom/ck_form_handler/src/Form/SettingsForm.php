<?php

namespace Drupal\ck_form_handler\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Tests\field\Functional\reEnableModuleFieldTest;

class SettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'ck_form_handler_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ck_form_handler.adminsettings');

    $form['telegram_bot_token'] = [
      '#type' => 'textfield',
      '#title' => 'токен Telegram бота',
      '#default_value' => $config->get('telegram_bot_token'),
    ];

    $form['telegram_chat_id'] = [
      '#type' => 'textfield',
      '#title' => 'идентификатор чата в который Telegram бот будет отправлять сообщения',
      '#default_value' => $config->get('telegram_chat_id'),
    ];

    $form['google_recaptcha_secret_key'] = [
      '#type' => 'textfield',
      '#title' => 'secret_key для Google Recaptcha',
      '#default_value' => $config->get('google_recaptcha_secret_key'),
    ];

    $form['smtp_email'] = [
      '#type' => 'textfield',
      '#title' => 'E-mail для smtp доступа',
      '#default_value' => $config->get('smtp_email'),
    ];

    $form['smtp_password'] = [
      '#type' => 'textfield',
      '#title' => 'Пароль для smtp доступа',
      '#default_value' => $config->get('smtp_password'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('ck_form_handler.adminsettings')
      ->set('telegram_bot_token', $form_state->getValue('telegram_bot_token'))
      ->set('telegram_chat_id', $form_state->getValue('telegram_chat_id'))
      ->set('google_recaptcha_secret_key', $form_state->getValue('google_recaptcha_secret_key'))
      ->set('smtp_email', $form_state->getValue('smtp_email'))
      ->set('smtp_password', $form_state->getValue('smtp_password'))
      ->save();
  }

  protected function getEditableConfigNames(): array {
    return [
      'ck_form_handler.adminsettings'
    ];
  }
}
