<?php
namespace Drupal\sitemap_additional_settings\Breadcrumb;

use Drupal\taxonomy\TermBreadcrumbBuilder;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatchInterface;

class TermSitemapAdditionalSettingsBreadcrumbBuilder extends TermBreadcrumbBuilder {

  public function applies(RouteMatchInterface $route_match) {
    $parameters = $route_match->getParameters()->all();
    return (isset($parameters['sitemap_additional_settings']) &&
      $parameters['sitemap_additional_settings'] &&
      (isset($parameters['taxonomy_term']))
    );
  }

}
