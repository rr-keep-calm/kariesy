<?php
/**
 * @file
 * Contains Drupal\price_page\PricePageBreadcrumbBuilder.
 */

namespace Drupal\price_page;

use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class PricePageBreadcrumbBuilder.
 *
 * @package Drupal\price_page;
 */
class PricePageBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route = $route_match->getCurrentRouteMatch();
    if ($route->getRouteObject()->getPath() == '/price/{service_type}') {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $params = $route_match->getRawParameters();
    $service_type = $params->get('service_type');
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route']);

    $links[] = Link::createFromRoute(t('Home'), '<front>');

    \Drupal::service('page_cache_kill_switch')->trigger();
    $service_types = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('service_type');
    $translitiration = new PhpTransliteration();
    foreach ($service_types as $service_types_item) {
      if (strtolower(str_replace(' ', '_', $translitiration->transliterate($service_types_item->name, 'en', '_'))) === $service_type) {
        $links[] = Link::createFromRoute('Цены на услугу "' . $service_types_item->name . '"', '<nolink>');
      }
    }
    if (count($links) < 2) {
      $links[] = Link::createFromRoute('Цены', '<nolink>');
    }

    $breadcrumb->setLinks($links);

    return $breadcrumb;
  }

}
