<?php
/**
 * @file
 * Contains \Drupal\price_page\Controller\PricePageController.
 */
namespace Drupal\yml_services_feed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class YmlServicesFeedController extends ControllerBase {

  public function servicesFeed() {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $response = new Response();
    $response->headers->set('Content-Type', 'text/xml');
    $response->setContent(file_get_contents(__DIR__ . '/../../feed/feed.xml'));

    return $response;
  }
}
