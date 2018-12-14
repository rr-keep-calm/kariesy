<?php
/**
 * @file
 * Contains \Drupal\price_page\Controller\PricePageController.
 */
namespace \Drupal\price_page\Controller;

use Drupal\Core\Controller\ControllerBase;

class PricePageController extends ControllerBase {

  public function pricePage() {
    $output = array();

    $output['#title'] = 'Цены';

    $output['#markup'] = 'Текст цен';

    return $output;
  }
}