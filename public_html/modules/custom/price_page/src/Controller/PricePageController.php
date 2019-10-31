<?php
/**
 * @file
 * Contains \Drupal\price_page\Controller\PricePageController.
 */

namespace Drupal\price_page\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\price_page\PricePageRepository;
use Drupal\Component\Transliteration\PhpTransliteration;

class PricePageController extends ControllerBase {

  public function pricePage($service_type) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $repo = new PricePageRepository();
    $pricePageData = $repo->getData($service_type);

    $title = 'Цены';
    \Drupal::service('page_cache_kill_switch')->trigger();
    $service_types = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('service_type');
    $translitiration = new PhpTransliteration();
    foreach ($service_types as $service_types_item) {
      if (strtolower(str_replace(' ', '_', $translitiration->transliterate($service_types_item->name, 'en', '_'))) === $service_type) {
        $title = 'Цены на услугу "' . $service_types_item->name . '"';
      }
    }
    return [
      '#theme' => 'price_page',
      '#title' => $title,
      '#data' => $pricePageData,
    ];
  }

  public function priceTitle($service_type) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $service_types = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('service_type');
    $translitiration = new PhpTransliteration();
    foreach ($service_types as $service_types_item) {
      if (strtolower(str_replace(' ', '_', $translitiration->transliterate($service_types_item->name, 'en', '_'))) === $service_type) {
        return 'Цены на услугу "' . $service_types_item->name . '" в стоматологии «Кариесу.нет»';
      }
    }
    return 'Цены на услуги в стоматологии «Кариесу.нет»';
  }
}
