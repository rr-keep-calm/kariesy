<?php

namespace Drupal\sitemap_additional_settings;


use Drupal\Core\Routing\RouteMatch;
use Drupal\taxonomy\Entity\Term;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class ProcessItems {
  public function processItem($item) {
    switch ($item) {
      case (isset($item['route_name']) && $item['route_name'] == 'entity.node.canonical') :
        $this->processNode($item);
        break;
      case (isset($item['route_name']) && $item['route_name'] == 'entity.taxonomy_term.canonical') :
        $this->processTerm($item);
        break;
      case (isset($item['path']) && preg_match('/^view/', $item['id']) ? TRUE : FALSE) :
        $this->processView($item);
        break;
    }
  }

  private function processNode($item) {
    $alias = \Drupal::service('path.alias_manager')
      ->getAliasByPath('/node/' . $item['id']);

    $request = Request::create($alias);
    $route_object = new Route($alias);
    $route_object->setDefault('sitemap_additional_settings', TRUE);
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $route_object);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, $item['route_name']);
    $request->attributes->set('sitemap_additional_settings', TRUE);
    $route = RouteMatch::createFromRequest($request);
    $breadcrumbHandler = \Drupal::service('breadcrumb');
    $breadcrumbs = $breadcrumbHandler->build($route);
    $links = [];
    foreach ($breadcrumbs->getLinks() as $link) {
      $link_url = $link->getUrl()->toString();
      if ($link_url === '/') {
        continue;
      }
      $links[] = $link_url;
    }

    $map_levels = \Drupal::state()->get('map_levels', []);
    $map_levels[count($links)][] = [
      'parent' => $links ? end($links) : '',
      'path' => $alias,
      'name' => $item['name'],
    ];
    \Drupal::state()->set('map_levels', $map_levels);
  }

  private function processTerm($item) {
    $alias = \Drupal::service('path.alias_manager')
      ->getAliasByPath('/taxonomy/term/' . $item['id']);

    $request = Request::create($alias);
    $route_object = new Route($alias);
    $route_object->setDefault('sitemap_additional_settings', TRUE);
    $route_object->setDefault('taxonomy_term', Term::load($item['id']));
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $route_object);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, $item['route_name']);
    $request->attributes->set('sitemap_additional_settings', TRUE);
    $request->attributes->set('taxonomy_term', Term::load($item['id']));
    $route = RouteMatch::createFromRequest($request);
    $breadcrumbHandler = \Drupal::service('breadcrumb');
    $breadcrumbs = $breadcrumbHandler->build($route);
    $links = [];
    foreach ($breadcrumbs->getLinks() as $link) {
      $link_url = $link->getUrl()->toString();
      if ($link_url === '/') {
        continue;
      }
      $links[] = $link_url;
    }

    if (\Drupal::hasService('kc_services.modify_breadcrumb')) {
      \Drupal::service('kc_services.modify_breadcrumb')
        ->modifyForInternals($links, $item['id']);
    }
    $map_levels = \Drupal::state()->get('map_levels', []);
    $map_levels[count($links)][] = [
      'parent' => $links ? end($links) : '',
      'path' => $alias,
      'name' => $item['name'],
    ];
    \Drupal::state()->set('map_levels', $map_levels);
  }

  private function processView($item) {
    $alias = '/' . $item['path'];

    $request = Request::create($alias);
    $route_object = new Route($alias);
    $route_object->setDefault('sitemap_additional_settings', TRUE);
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, $route_object);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, $item['id']);
    $request->attributes->set('sitemap_additional_settings', TRUE);
    $route = RouteMatch::createFromRequest($request);
    $breadcrumbHandler = \Drupal::service('breadcrumb');
    $breadcrumbs = $breadcrumbHandler->build($route);
    $links = [];
    foreach ($breadcrumbs->getLinks() as $link) {
      $link_url = $link->getUrl()->toString();
      if ($link_url === '/') {
        continue;
      }
      $links[] = $link_url;
    }

    $map_levels = \Drupal::state()->get('map_levels', []);
    $map_levels[count($links)][] = [
      'parent' => $links ? end($links) : '',
      'path' => $alias,
      'name' => $item['name'],
    ];
    \Drupal::state()->set('map_levels', $map_levels);
  }
}
