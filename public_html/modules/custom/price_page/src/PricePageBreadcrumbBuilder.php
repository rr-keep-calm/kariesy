<?php
/**
 * @file
 * Contains Drupal\price_page\PricePageBreadcrumbBuilder.
 */

namespace Drupal\price_page;

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
    if ($route->getRouteObject()->getPath() == '/price') {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route']);

    $links[] = Link::createFromRoute(t('Home'), '<front>');
    $links[] = Link::createFromRoute('Цены', '<nolink>');

    $breadcrumb->setLinks($links);

    return $breadcrumb;
  }

}