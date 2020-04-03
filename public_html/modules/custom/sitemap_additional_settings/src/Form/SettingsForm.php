<?php

namespace Drupal\sitemap_additional_settings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\system\Entity\Menu;
use Drupal\Core\Link;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\views\Views;

class SettingsForm extends ConfigFormBase {

  public function getFormId(): string {
    return 'sitemap_additional_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $sitemapModuleConfig = $this->configFactory->get('sitemap.settings');
    $config = $this->config('sitemap_additional.adminsettings');

    $available_menus = $sitemapModuleConfig->get('show_menus');
    if ($available_menus) {
      $available_menus = array_filter($available_menus);
      foreach ($available_menus as &$available_menu) {
        $available_menu = Menu::load($available_menu)->label();
      }
      $form['menu_for_auto_add'] = array(
        '#type' => 'select',
        '#title' => 'Меню в которое автоматически будут добавляться новые ссылки',
        '#default_value' => $config->get('menu_for_auto_add'),
        '#options' => ['0' => 'Отключить автоматическое добавление новых ссылок'] + $available_menus,
        '#multiple' => FALSE,
      );
    } else {
      $link = Link::createFromRoute('Sitemap', 'sitemap.settings')->toString();
      $form['test'] = ["#markup" => '<b>Не выбрано ни одного меню для включения
в карту сайта. Это необходимо для работы автоматического добавления. Пожалуйста
выбирете меню в настройках модуля ' . $link . '!</b>'];
    }

    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('taxonomy')) {
      $vocab_options = array();
      $vocabularies = Vocabulary::loadMultiple();
      foreach ($vocabularies as $vocabulary) {
        $vocab_options[$vocabulary->id()] = $vocabulary->label();
        $sitemap_ordering['vocabularies_' . $vocabulary->id()] = $vocabulary->label();
      }
      $form['exclude_vocabularies'] = array(
        '#type' => 'checkboxes',
        '#title' => 'Словари элементы которых не будут обрабатываться для автоматического добавления',
        '#default_value' => $config->get('exclude_vocabularies'),
        '#options' => $vocab_options,
        '#multiple' => TRUE,
      );
    }

    $types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
    foreach ($types as &$type) {
      $type = $type->label();
    }
    $form['exclude_node_types'] = array(
      '#type' => 'checkboxes',
      '#title' => 'Типы материалов элементы которых не будут обрабатываться для автоматического добавления',
      '#default_value' => $config->get('exclude_node_types'),
      '#options' => $types,
      '#multiple' => TRUE,
    );

    if ($moduleHandler->moduleExists('views')) {
      $views_list = Views::getAllViews();
      foreach ($views_list as &$views) {
        $views = $views->label();
      }
      $form['views_for_auto_add'] = array(
        '#type' => 'checkboxes',
        '#title' => 'Представления элементы которых (являющиеся страницами) будут обрабатываться для автоматического добавления',
        '#default_value' => $config->get('views_for_auto_add'),
        '#options' => $views_list,
        '#multiple' => TRUE,
      );
    }

    $form['custom_map_links'] = array(
      '#type' => 'textarea',
      '#title' => 'Произвольные ссылки которые будут добалены в карту сайта при пересборе',
      '#description' => "Формат добавления \"название|ссылка|родительский url\" (без кавычек), если родительский url не указан, то ссылка будет добавлена на первый уровень одна ссылка на строку",
      '#default_value' => $config->get('custom_map_links'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $custom_map_links = $form_state->getValue('custom_map_links');
    foreach (explode("\n", $custom_map_links) as $custom_map_link) {
      if (count(explode('|', $custom_map_link)) < 2) {
        $form_state->setErrorByName('custom_map_links', 'Кастомная ссылка "' . $custom_map_link . '" не соответствуюет формату. Обратите внимание на описание в поле.');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('sitemap_additional.adminsettings')
      ->set('menu_for_auto_add', $form_state->getValue('menu_for_auto_add'))
      ->set('exclude_vocabularies', $form_state->getValue('exclude_vocabularies'))
      ->set('exclude_node_types', $form_state->getValue('exclude_node_types'))
      ->set('views_for_auto_add', $form_state->getValue('views_for_auto_add'))
      ->set('custom_map_links', $form_state->getValue('custom_map_links'))
      ->save();
  }

  protected function getEditableConfigNames(): array {
    return [
      'sitemap_additional.adminsettings'
    ];
  }
}
