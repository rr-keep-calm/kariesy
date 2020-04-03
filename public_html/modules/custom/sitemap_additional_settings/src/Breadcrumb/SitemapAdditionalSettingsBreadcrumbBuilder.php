<?php
namespace Drupal\sitemap_additional_settings\Breadcrumb;

use Drupal\system\PathBasedBreadcrumbBuilder;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

class SitemapAdditionalSettingsBreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  public function applies(RouteMatchInterface $route_match) {
    $parameters = $route_match->getParameters()->all();
    return (isset($parameters['sitemap_additional_settings']) && $parameters['sitemap_additional_settings']);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {

    /// Set request context from passed in $route_match
    $url = $route_match->getRouteObject()->getPath();
    if ($request = $this->getRequestForPath($url, [])) {
      $context = new RequestContext();
      $context->fromRequest($request);
      $this->context = $context;
    }

    // Build breadcrumbs using new context ($route_match is unused)
    return parent::build($route_match);
  }

}
