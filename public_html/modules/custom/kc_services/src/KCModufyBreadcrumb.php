<?php

namespace Drupal\kc_services;

use Drupal\taxonomy\Entity\Term;
use Drupal\views\Views;

class KCModufyBreadcrumb {
  public function modify(&$variables) {
    $is_service_term = FALSE;
    $serviceTypeBread = [];
    if (\Drupal::routeMatch()->getRouteName() == 'entity.taxonomy_term.canonical' && $tid = \Drupal::routeMatch()->getRawParameter('taxonomy_term')) {
      $this->modifyForTaxonomyTerms($tid, $is_service_term, $serviceTypeBread);
    }
    if ($variables['breadcrumb']) {
      if ($is_service_term) {
        $breadcrumbItem = $this->getStaticBreadcrumbItem();
        $variables['breadcrumb'] = array_merge(
          array_slice($variables['breadcrumb'], 0, 1),
          [$breadcrumbItem],
          $serviceTypeBread,
          array_slice($variables['breadcrumb'], 1)
        );
      }
      $request = \Drupal::request();
      $route_match = \Drupal::routeMatch();
      $page_title = \Drupal::service('title_resolver')
        ->getTitle($request, $route_match->getRouteObject());

      if ($page_title && \Drupal::routeMatch()->getRouteName() != 'price_page.price_page') {
        $variables['breadcrumb'][] = [
          'text' => $page_title,
        ];
      }
    }
    $variables['#cache']['contexts'][] = 'url';
  }

  public function modifyForInternals(&$links, $tid) {
    $is_service_term = FALSE;
    $serviceTypeBread = [];
    $this->modifyForTaxonomyTerms($tid, $is_service_term, $serviceTypeBread);
    array_walk($serviceTypeBread, function (&$item) {
      $item = $item['url'];
    });
    if ($is_service_term) {
      $breadcrumbItem = $this->getStaticBreadcrumbItemUrl();
      $links = array_merge(
        [$breadcrumbItem],
        $serviceTypeBread,
        $links
      );
    }
  }

  private function modifyForTaxonomyTerms($tid, &$is_service_term = FALSE, &$serviceTypeBread = []) {
    $term = Term::load($tid);
    $vacabulary_id = $term->getVocabularyId();
    // Получаем тип услуги для вставик его в хлебные крошки
    if ($vacabulary_id == 'service') {
      $serviceType = $term->get('field_service_type')->getValue();
      if ($serviceType) {
        $serviceTypeTerm = Term::load($serviceType[0]['target_id']);
        $parent_view = Views::getView('services_page');
        $displays = $parent_view->storage->get('display');
        foreach ($displays as $display) {
          if (reset($display['display_options']['filters']['field_service_type_target_id']['value']) == $serviceTypeTerm->id()) {
            $name = $display['display_title'];
            $url = '/' . $display['display_options']['path'];
          }
        }
        $serviceTypeBread[] = ['text' => $name, 'url' => $url];
      }
      $is_service_term = TRUE;
    }
  }

  private function getStaticBreadcrumbItem() {
    return ['text' => 'Услуги', 'url' => $this->getStaticBreadcrumbItemUrl()];
  }

  private function getStaticBreadcrumbItemUrl() {
    return '/uslugi';
  }
}
