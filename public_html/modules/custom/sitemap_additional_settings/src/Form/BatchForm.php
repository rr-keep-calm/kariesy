<?php

namespace Drupal\sitemap_additional_settings\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatch;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

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
    $config = $this->config('sitemap_additional.adminsettings');
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
    // Очищаем все ранее сохранённые данные для построения карты сайта
    $config = $this->config('sitemap_additional.adminsettings');
    $nodes = $this->getNodes(array_filter($config->get('exclude_node_types')));
    $a = 1;

    $this->batchBuilder
      ->setTitle($this->t('Processing'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));

    $this->batchBuilder->setFile(drupal_get_path('module', 'sitemap_additional_settings') . '/src/Form/BatchForm.php');
    $this->batchBuilder->addOperation([$this, 'processItems'], [$nodes]);
    $this->batchBuilder->setFinishCallback([$this, 'finished']);

    batch_set($this->batchBuilder->toArray());
  }

  /**
   * Processor for batch operations.
   */
  public function processItems($items, array &$context) {
    // limit in seconds
    $limit = 50;
    $start_time = time();

    // Set default progress values.
    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($items);
    }

    // Save items to array which will be changed during processing.
    if (empty($context['sandbox']['items'])) {
      $context['sandbox']['items'] = $items;
    }

    if (!empty($context['sandbox']['items'])) {
      // Remove already processed items.
      if ($context['sandbox']['progress'] != 0) {
        array_splice($context['sandbox']['items'], 0, $context['sandbox']['progress']);
      }

      foreach ($context['sandbox']['items'] as $item) {
        if (time() - $start_time < $limit) {
          $this->processItem($item);

          $context['sandbox']['progress']++;

          $context['message'] = $this->t('Now processing node :progress of :count', [
            ':progress' => $context['sandbox']['progress'],
            ':count' => $context['sandbox']['max'],
          ]);

          // Increment total processed item values. Will be used in finished
          // callback.
          $context['results']['processed'] = $context['sandbox']['progress'];
        }
      }
    }

    // If not finished all tasks, we count percentage of process. 1 = 100%.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Process single item.
   *
   * @param int|string $nid
   *   An id of Node.
   */
  public function processItem($nid) {
    /** @var \Drupal\node\NodeInterface $node */
    $alias = \Drupal::service('path.alias_manager')
      ->getAliasByPath('/node/' . $nid);

    /* @var $breadcrumbHandler \Drupal\Core\Breadcrumb\BreadcrumbManager */
    $request = Request::create($alias);
    $route_object = new Route($alias);
    $route_object->setDefault('sitemap_additional_settings', true);
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $route_object);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'entity.node.canonical');
    $request->attributes->set('sitemap_additional_settings', true);
    $route = RouteMatch::createFromRequest($request);
    $breadcrumbHandler = \Drupal::service('breadcrumb');
    $breadcrumbs = $breadcrumbHandler->build($route);
    $a = 1;
  }

  /**
   * Finished callback for batch.
   */
    public
    function finished($success, $results, $operations) {
      $message = $this->t('Number of nodes affected by batch: @count', [
        '@count' => $results['processed'],
      ]);

      $this->messenger()
        ->addStatus($message);
    }

    /**
     * Load all nids without specific type.
     *
     * @return array
     *   An array with nids.
     */
    public
    function getNodes($exclude_node_types) {
      return \Drupal::entityQuery('node')
        ->condition('status', NodeInterface::PUBLISHED)
        ->condition('type', $exclude_node_types, 'NOT IN')
        ->execute();
    }
  }
