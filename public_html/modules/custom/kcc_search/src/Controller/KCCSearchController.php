<?php

namespace Drupal\kcc_search\Controller;

use Drupal\Core\Controller\ControllerBase;

class KCCSearchController extends ControllerBase {

  public function search() {
    // Страница поиска никогда не должна кэшироваться
    \Drupal::service('page_cache_kill_switch')->trigger();

    $search_process = \Drupal::service('kcc_search.process');
    $search_result = $search_process->search();

    return [
      '#theme' => 'kcc_search',
      '#title' => 'Поиск',
      '#data' => [
        'items' => $search_result,
        'keys' => (string) \Drupal::request()->get('keys'),
        'page' => (int) \Drupal::request()->get('page'),
      ],
    ];
  }
}