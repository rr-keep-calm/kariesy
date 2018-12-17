<?php
/**
 * @file
 * Contains \Drupal\price_page\Controller\PricePageController.
 */
namespace Drupal\price_page\Controller;

use Drupal\Core\Controller\ControllerBase;

class PricePageController extends ControllerBase {

  public function pricePage() {

    return [
      '#theme' => 'price_page',
      '#title' => 'Цены',
      '#test_var' => $this->t('Test Value'),
    ];

  }
}