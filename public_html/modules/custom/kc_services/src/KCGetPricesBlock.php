<?php

namespace Drupal\kc_services;

use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Views;

class KCGetPricesBlock {

  public function get($term) {
    $result = ['content' => NULL, 'last_part_url' => NULL, 'block_header' => NULL];
    $render = $this->getRenderedViews($term);
    if (count($render['#rows'][0]['#rows']) > 0) {
      $result['content'] = \Drupal::service('renderer')->render($render);
      $result['last_part_url'] = $this->getLastPartUrl($term);
      $result['block_header'] = $this->getBlockHeader($term);
    }
    return $result;
  }

  private function getRenderedViews($term) {
    $render = NULL;
    if ($term->getVocabularyId() == 'service') {
      $view = Views::getView('blok_cen_na_stranice_uslugi');
      $view->setDisplay('block_1');
      $view->setArguments([$term->id()]);
      $render = $view->render();
    }
    if ($term->getVocabularyId() == 'service_type') {
      $view = Views::getView('blok_cen_na_stranice_kategorii');
      $view->setDisplay('block_1');
      $prices_nids = array_map(function ($item) {
        return $item['target_id'];
      }, $term->get('field_prices')->getValue());
      $view->setArguments([implode(',', $prices_nids)]);
      $render = $view->render();
    }
    return $render;
  }

  private function getLastPartUrl($term) {
    $last_part_url = NULL;
    if ($term->getVocabularyId() == 'service') {
      $service_type = $term->get('field_service_type')->getValue();
      if (isset($service_type[0]) && isset($service_type[0]['target_id'])) {
        $service_type = Term::load($service_type[0]['target_id']);
        $service_type_name = $service_type->name->value;
        $translitiration = new PhpTransliteration();
        $last_part_url = strtolower(str_replace(' ', '_', $translitiration->transliterate($service_type_name, 'en', '_')));
      }
    }
    if ($term->getVocabularyId() == 'service_type') {
      $translitiration = new PhpTransliteration();
      $last_part_url = strtolower(str_replace(' ', '_', $translitiration->transliterate($term->name->value, 'en', '_')));
    }
    return $last_part_url;
  }

  private function getBlockHeader($term) {
    $block_header = NULL;
    if ($term->getVocabularyId() == 'service_type') {
      $block_header_field_value = $term->get('field_prices_block_header')->getValue();
      if ($block_header_field_value) {
        $block_header = $block_header_field_value[0]['value'];
      }
    }
    return $block_header;
  }
}
