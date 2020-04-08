<?php

namespace Drupal\sitemap_additional_settings\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuTreeStorage;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatch;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Drupal\Core\Menu\MenuTreeParameters;

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
    \Drupal::state()->set('map_levels', []);
    // Очищаем все ранее сохранённые данные для построения карты сайта
    $config = $this->config('sitemap_additional.adminsettings');

    //TODO получить от каждой сущности заголовок для использования в построении меню

    // Получаем все ноды
    $nodes = $this->getNodes(array_filter($config->get('exclude_node_types')));

    // Получаем все словари таксономии
    $terms = $this->getTerms(array_filter($config->get('exclude_vocabularies')));

    // Получаем все страницы представлений
    $views = $this->getViews(array_filter($config->get('views_for_auto_add')));

    $this->batchBuilder
      ->setTitle($this->t('Processing'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));

    $this->batchBuilder->setFile(drupal_get_path('module', 'sitemap_additional_settings') . '/src/Form/BatchForm.php');
    $this->batchBuilder->addOperation([$this, 'processItems'], [
      [$nodes,
      $terms,
      $views]
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
      $context['sandbox']['max'] = array_reduce($items, function($count,$item) {
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
          } else {
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
          } else {
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
    switch ($item) {
      case (isset($item['route_name']) && $item['route_name'] == 'entity.node.canonical') :
        $this->processNode($item);
        break;
      case (isset($item['route_name']) && $item['route_name'] == 'entity.taxonomy_term.canonical') :
        $this->processTerm($item);
        break;
      case (isset($item['path']) && preg_match('/^view/', $item['id']) ? true : false) :
        $this->processView($item);
        break;
    }
  }

  public function processNode($item) {
    $alias = \Drupal::service('path.alias_manager')
      ->getAliasByPath('/node/' . $item['id']);

    $request = Request::create($alias);
    $route_object = new Route($alias);
    $route_object->setDefault('sitemap_additional_settings', TRUE);
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $route_object);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, $item['route_name']);
    $request->attributes->set('sitemap_additional_settings', TRUE);
    $route = RouteMatch::createFromRequest($request);
    $breadcrumbHandler = \Drupal::service('breadcrumb');
    $breadcrumbs = $breadcrumbHandler->build($route);
    $links = [];
    foreach ($breadcrumbs->getLinks() as $link) {
      $link_url = $link->getUrl()->toString();
      if ($link_url === '/') {
        continue;
      }
      $links[] = $link_url;
    }

    $map_levels = \Drupal::state()->get('map_levels', []);
    $map_levels[count($links)][] = ['parent' => $links ? end($links) : '', 'path' => $alias, 'name' => $item['name']];
    \Drupal::state()->set('map_levels', $map_levels);
  }

  public function processTerm($item) {
    $alias = \Drupal::service('path.alias_manager')
      ->getAliasByPath('/taxonomy/term/' . $item['id']);

    $request = Request::create($alias);
    $route_object = new Route($alias);
    $route_object->setDefault('sitemap_additional_settings', TRUE);
    $route_object->setDefault('taxonomy_term', Term::load($item['id']));
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $route_object);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, $item['route_name']);
    $request->attributes->set('sitemap_additional_settings', TRUE);
    $request->attributes->set('taxonomy_term', Term::load($item['id']));
    $route = RouteMatch::createFromRequest($request);
    $breadcrumbHandler = \Drupal::service('breadcrumb');
    $breadcrumbs = $breadcrumbHandler->build($route);
    $links = [];
    foreach ($breadcrumbs->getLinks() as $link) {
      $link_url = $link->getUrl()->toString();
      if ($link_url === '/') {
        continue;
      }
      $links[] = $link_url;
    }

    if (\Drupal::hasService('kc_services.modify_breadcrumb')) {
      \Drupal::service('kc_services.modify_breadcrumb')->modifyForInternals($links, $item['id']);
    }
    $map_levels = \Drupal::state()->get('map_levels', []);
    $map_levels[count($links)][] = ['parent' => $links ? end($links) : '', 'path' => $alias, 'name' => $item['name']];
    \Drupal::state()->set('map_levels', $map_levels);
  }

  public function processView($item) {
    $alias = '/' . $item['path'];

    $request = Request::create($alias);
    $route_object = new Route($alias);
    $route_object->setDefault('sitemap_additional_settings', TRUE);
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $route_object);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, $item['id']);
    $request->attributes->set('sitemap_additional_settings', TRUE);
    $route = RouteMatch::createFromRequest($request);
    $breadcrumbHandler = \Drupal::service('breadcrumb');
    $breadcrumbs = $breadcrumbHandler->build($route);
    $links = [];
    foreach ($breadcrumbs->getLinks() as $link) {
      $link_url = $link->getUrl()->toString();
      if ($link_url === '/') {
        continue;
      }
      $links[] = $link_url;
    }

    $map_levels = \Drupal::state()->get('map_levels', []);
    $map_levels[count($links)][] = ['parent' => $links ? end($links) : '', 'path' => $alias, 'name' => $item['name']];
    \Drupal::state()->set('map_levels', $map_levels);
  }

  /**
   * Finished callback for batch.
   */
  public function finished($success, $results, $operations) {
    $map_levels = \Drupal::state()->get('map_levels', []);
    ksort($map_levels);
    $config = $this->config('sitemap_additional.adminsettings');

    // Для начала удаляем ссылки которые удаляются наверняка - это добавленные
    // непосредственно в меню
    $mids = \Drupal::entityQuery('menu_link_content')
      ->condition('menu_name', $config->get('menu_for_auto_add'))
      ->execute();
    $controller = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $entities = $controller->loadMultiple($mids);
    $controller->delete($entities);

    // Если остались ссылки с большой долей вероятности это ссылки созданные из
    // views. Программно мы на них повлиять не можем, а знаичт потребуется
    // ручное вмешательство
    $parents = [];
    $host = \Drupal::request()->getSchemeAndHttpHost();
    foreach ($map_levels as $map_level) {
      foreach ($map_level as $level_item) {
        if ($level_item['path'] == '/') {
          continue;
        }
        $menu_link = [
          'title' => $level_item['name'],
          'link' => ['uri' => $host . $level_item['path']],
          'menu_name' => $config->get('menu_for_auto_add'),
          'expanded' => TRUE,
        ];
        if ($level_item['parent'] && isset($parents[$level_item['parent']])) {
          $menu_link['parent'] = $parents[$level_item['parent']];
        }
        $menu_link_content = MenuLinkContent::create($menu_link);
        $menu_link_content->save();
        $parents[$level_item['path']] = $menu_link_content->getPluginId();
      }
    }
    $custom_map_links = $config->get('custom_map_links');
    foreach (explode("\n", $custom_map_links) as $custom_map_link) {
      $custom_map_link_parts = explode('|', $custom_map_link);
      if ($custom_map_link_parts[1] == '/') {
        continue;
      }
      $menu_link = [
        'title' => $custom_map_link_parts[0],
        'link' => ['uri' => $host . $custom_map_link_parts[1]],
        'menu_name' => $config->get('menu_for_auto_add'),
        'expanded' => TRUE,
      ];
      if (isset($custom_map_link_parts[2]) && isset($parents[trim($custom_map_link_parts[2])])) {
        $menu_link['parent'] = $parents[trim($custom_map_link_parts[2])];
      }
      $menu_link_content = MenuLinkContent::create($menu_link);
      $menu_link_content->save();
      $parents[trim($custom_map_link_parts[1])] = $menu_link_content->getPluginId();
    }

    $cache = \Drupal::cache('menu');
    $cache->deleteAll();
    drupal_flush_all_caches();

    $message = $this->t('Number of items affected by batch: @count', [
      '@count' => $results['processed'],
    ]);

    $this->messenger()
      ->addStatus($message);
  }

  /**
   * Load all nids without specific type.
   *
   * @return array
   *   An array with nids and route name.
   */
  public function getNodes($exclude_node_types) {
    $nodes = \Drupal::entityQuery('node')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', $exclude_node_types, 'NOT IN')
      ->execute();
    $nodes = Node::loadMultiple($nodes);
    return array_map(function ($node) {
      return ['id' => $node->id(), 'route_name' => 'entity.node.canonical', 'name' => $node->label()];
    }, $nodes);
  }

  /**
   * Load all tids without specific type.
   *
   * @return array
   *   An array with tids and route name.
   */
  public function getTerms($exclude_vocabularies) {
    $vids = Vocabulary::loadMultiple();
    $vids = array_filter(array_keys($vids), function ($item) use ($exclude_vocabularies) {
      return !isset($exclude_vocabularies[$item]);
    });
    $terms = [];
    foreach ($vids as $vid) {
      $terms_temp = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadTree($vid);
      foreach ($terms_temp as $term_temp) {
        $terms[] = ['id' => $term_temp->tid, 'route_name' => 'entity.taxonomy_term.canonical', 'name' => $term_temp->name];
      }
    }
    return $terms;
  }

  /**
   * Load all views pages without specific type.
   *
   * @return array
   *   An array with path views page and route name.
   */
  public function getViews($views_for_auto_add) {
    $views = [];
    foreach ($views_for_auto_add as $view_for_auto_add) {
      $view = Views::getView($view_for_auto_add);
      foreach ($view->storage->get('display') as $display) {
        if ($display['display_plugin'] !== 'page') {
          continue;
        }
        $view->setDisplay($display['id']);
        $views[] = ['id' => 'view.' . $view->id() . '.' . $display['id'], 'path' => $display['display_options']['path'], 'name' => $view->getTitle()];
      }
    }
    return $views;
  }
}
