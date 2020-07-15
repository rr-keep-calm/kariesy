<?php
namespace Drupal\sitemap_additional_settings;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\views\Views;

class GetEntitiesItems {
  public function getEntitiesItems()
  {
    $entities = [];

    // Очищаем все ранее сохранённые данные для построения карты сайта
    $config = \Drupal::config('sitemap_additional_settings.adminsettings');

    // Получаем все ноды
    $entities['nodes'] = $this->getNodes(array_filter($config->get('exclude_node_types')));

    // Получаем все словари таксономии
    $entities['terms'] = $this->getTerms(array_filter($config->get('exclude_vocabularies')));

    // Получаем все страницы представлений
    $entities['views'] = $this->getViews(array_filter($config->get('views_for_auto_add')));

    return $entities;
  }



  /**
   * Load all nids without specific type.
   *
   * @return array
   *   An array with nids and route name.
   */
  private function getNodes($exclude_node_types) {
    $nodes = \Drupal::entityQuery('node')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', $exclude_node_types, 'NOT IN')
      ->condition('nid', 220, '!=')  // Убираем из результатов поиска Сапельникова Александра Александровича
      ->execute();
    $nodes = Node::loadMultiple($nodes);
    return array_map(function ($node) {
      return [
        'id' => $node->id(),
        'route_name' => 'entity.node.canonical',
        'name' => $node->label(),
      ];
    }, $nodes);
  }

  /**
   * Load all tids without specific type.
   *
   * @return array
   *   An array with tids and route name.
   */
  private function getTerms($exclude_vocabularies) {
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
        if (!$term_temp->status) {
          continue;
        }
        $terms[] = [
          'id' => $term_temp->tid,
          'route_name' => 'entity.taxonomy_term.canonical',
          'name' => $term_temp->name,
        ];
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
  private function getViews($views_for_auto_add) {
    $views = [];
    foreach ($views_for_auto_add as $view_for_auto_add) {
      $view = Views::getView($view_for_auto_add);
      foreach ($view->storage->get('display') as $display) {
        if ($display['display_plugin'] !== 'page') {
          continue;
        }
        if ($display['display_options']['enabled'] === FALSE) {
          continue;
        }
        $view->setDisplay($display['id']);
        $views[] = [
          'id' => 'view.' . $view->id() . '.' . $display['id'],
          'path' => $display['display_options']['path'],
          'name' => $view->getTitle(),
        ];
      }
    }
    return $views;
  }
}
