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

    $title = 'Цены на услуги';
    \Drupal::service('page_cache_kill_switch')->trigger();
    $service_types = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('service_type');
    $translitiration = new PhpTransliteration();
    foreach ($service_types as $service_types_item) {
      $service_types_item_name = strtolower($translitiration->transliterate($service_types_item->name, 'en', '_'));
      $service_types_item_name = str_replace([' ', 'kh', 'KH', 'Kh', 'kH'], ['-', 'h', 'H', 'H', 'h'], $service_types_item_name);
      if ($service_types_item_name === $service_type) {
        switch ($service_types_item->tid) {
          case 3:
            $title = 'Цены на лечение зубов';
            break;
          case 4:
            $title = 'Цены на имплантацию зубов';
            break;
          case 8:
            $title = 'Цены на протезирование зубов';
            break;
          case 9:
            $title = 'Цены на хирургическую стоматологию';
            break;
          case 10:
            $title = 'Цены на ортодонтические услуги';
            break;
          case 11:
            $title = 'Цены на услуги профессиональной гигиены ';
            break;
          case 12:
            $title = 'Цены на услуги детской стоматологии';
            break;
          case 157:
            $title = 'Цены на услуги пародонтологии';
            break;
          case 158:
            $title = 'Цены на услуги диагностики';
            break;
        }
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
      $service_types_item_name = strtolower($translitiration->transliterate($service_types_item->name, 'en', '_'));
      $service_types_item_name = str_replace([' ', 'kh', 'KH', 'Kh', 'kH'], ['-', 'h', 'H', 'H', 'h'], $service_types_item_name);
      if ($service_types_item_name === $service_type) {
        $title = '';
        switch ($service_types_item->tid) {
          case 3:
            $title = 'Цены на лечение зубов в Москве';
            break;
          case 4:
            $title = 'Цены на имплантацию зубов в Москве';
            break;
          case 8:
            $title = 'Цены на протезирование зубов в Москве';
            break;
          case 9:
            $title = 'Цены на хирургическую стоматологию в Москве';
            break;
          case 10:
            $title = 'Цены на ортодонтические услуги в Москве';
            break;
          case 11:
            $title = 'Цены на услуги профессиональной гигиены полости рта и зубов в Москве';
            break;
          case 12:
            $title = 'Цены на услуги детской стоматологии в Москве';
            break;
          case 157:
            $title = 'Цены на услуги пародонтологии в Москве';
            break;
          case 158:
            $title = 'Цены на услуги диагностики в Москве';
            break;
        }
        return $service_types_item->tid . '|||' . $title;
      }
    }
    return 'Цены на услуги в стоматологии Кариесу.нет';
  }
}
