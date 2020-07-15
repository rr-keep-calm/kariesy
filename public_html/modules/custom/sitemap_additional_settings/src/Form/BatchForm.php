<?php

namespace Drupal\sitemap_additional_settings\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BatchForm extends FormBase {

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * BatchForm constructor.
   */
  public function __construct() {
    $this->batchBuilder = new BatchBuilder();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sitemap_additional_settings_batch';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['help'] = [
      '#markup' => $this->t('Пересбор меню которое служит основанием для вывода информации на странице карты сайта'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['run'] = [
      '#type' => 'submit',
      '#value' => 'Зпустить процессс пересбора',
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('sitemap_additional_settings.adminsettings');
    if (!$config->get('menu_for_auto_add')) {
      $form_state->setErrorByName('actions', 'Не выбрано меню в которое автоматически будут добавляться новые ссылки');
      return;
    }
    $sitemap_module_config = $this->configFactory->get('sitemap.settings');
    if (!in_array($config->get('menu_for_auto_add'), array_filter($sitemap_module_config->get('show_menus')))) {
      $form_state->setErrorByName('actions', 'Выбранное меню не активно в натсройках основного модуля');
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::state()->set('map_levels', []);

    $entities = \Drupal::service('sitemap_additional_settings.get_entities_items')->getEntitiesItems();

    $this->batchBuilder
      ->setTitle($this->t('Processing'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));

    $this->batchBuilder->setFile(drupal_get_path('module', 'sitemap_additional_settings') . '/src/Form/BatchForm.php');
    $this->batchBuilder->addOperation([$this, 'processItems'], [
      $entities,
    ]);
    $this->batchBuilder->setFinishCallback([$this, 'finished']);

    batch_set($this->batchBuilder->toArray());
  }

  /**
   * Processor for batch operations.
   */
  public function processItems($items, array &$context) {
    // limit in seconds
    $limit = 40;
    $start_time = time();

    // Set default progress values.
    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = array_reduce($items, function ($count, $item) {
        $count += count($item);
        return $count;
      });
    }

    // Save items to array which will be changed during processing.
    if (empty($context['sandbox']['items'])) {
      $context['sandbox']['all_items'] = $context['sandbox']['items'] = $items;
    }

    if (!empty($context['sandbox']['items'])) {
      if ($context['sandbox']['progress'] != 0) {
        $context['sandbox']['all_items_temp'] = $context['sandbox']['all_items'];
        $progress = $context['sandbox']['progress'];
        foreach ($context['sandbox']['all_items'] as $key => $all_item) {
          if (!$progress) {
            break;
          }
          if (count($all_item) <= $progress) {
            unset($context['sandbox']['all_items'][$key]);
            $progress -= count($all_item);
          }
          else {
            array_splice($context['sandbox']['all_items'][$key], 0, $progress);
            $progress = 0;
          }
        }
        $context['sandbox']['items'] = $context['sandbox']['all_items'];
        $context['sandbox']['all_items'] = $context['sandbox']['all_items_temp'];
      }

      foreach ($context['sandbox']['items'] as $items) {
        foreach ($items as $key => $item) {
          if (time() - $start_time < $limit) {
            $this->processItem($item);

            $context['sandbox']['progress']++;

            $context['message'] = $this->t('Now processing node :progress of :count', [
              ':progress' => $context['sandbox']['progress'],
              ':count' => $context['sandbox']['max'],
            ]);
          }
          else {
            break 2;
          }
        }
      }
      $context['results']['processed'] = $context['sandbox']['progress'];
    }

    // If not finished all tasks, we count percentage of process. 1 = 100%.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Process single item.
   *
   * @param array $item
   *   An id of Entity item.
   */
  public function processItem($item) {
    \Drupal::service('sitemap_additional_settings.process_items')->processItem($item);
  }

  /**
   * Finished callback for batch.
   */
  public function finished($success, $results, $operations) {
    \Drupal::service('sitemap_additional_settings.refill_site_map_menu')->refillSiteMapMenu();

    $message = $this->t('Number of items affected by batch: @count', [
      '@count' => $results['processed'],
    ]);

    $this->messenger()
      ->addStatus($message);
  }
}
