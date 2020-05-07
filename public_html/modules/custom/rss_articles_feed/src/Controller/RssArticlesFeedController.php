<?php
/**
 * @file
 * Contains \Drupal\price_page\Controller\PricePageController.
 */
namespace Drupal\rss_articles_feed\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class RssArticlesFeedController extends ControllerBase {

  public function articlesFeed() {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $response = new Response();
    $response->headers->set('Content-Type', 'text/xml');
    $response->setContent(file_get_contents(__DIR__ . '/../../feed/feed.xml'));

    return $response;
  }
}
