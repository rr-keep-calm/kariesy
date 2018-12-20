<?php
/**
 * @file
 * Contains \Drupal\price_page\Controller\PricePageController.
 */
namespace Drupal\price_page\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\price_page\PricePageRepository;

class PricePageController extends ControllerBase {

  public function pricePage() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $repo = new PricePageRepository();
    $pricePageData = $repo->getData();
    return [
      '#theme' => 'price_page',
      '#title' => 'Цены',
      '#data' => $pricePageData
    ];

  }
}