<?php

namespace Drupal\kcc_search;


use Drupal\Core\Database\Query\Condition;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

class KCCSearchProcess {

  private $search_result = [];

  private $search_string;

  public function search() {
    //TODO поиск по разбитым частям предложения
    $this->getSearchString();

    if (!$this->search_string) {
      return [];
    }

    $this->searchByTerms();
    $this->searchByNodes();
    // TODO организовать поиск по данным параграфов
    // TODO организовать поиск по глобальным полям вьюсов в типах услуг

    $this->handleSearchResult();
    // TODO сортировка данных по релевантности

    return $this->search_result;
  }

  private function getSearchString() {
    $this->search_string = \Drupal::request()->get('keys');
  }

  private function searchByNodes() {
    $items = array_keys(NodeType::loadMultiple());

    // исключаем поиск по отзывам
    if ($key = array_search('review', $items)) {
      unset($items[$key]);
    }

    // исключаем поиск по вопорсам
    if ($key = array_search('vopros_otvet', $items)) {
      unset($items[$key]);
    }

    // TODO поиск по отзывам и по вопорсам должен уводить на страницу где
    // выводится этот отзыв или вопрос в списке других

    $field_tables = ['node_field_data' => []];

    $this->collectFieldTables('node', $items, 'nid', $field_tables);
    $condition_and = new Condition('AND');
    $condition_and->condition('node_field_data.type', 'review', '!=');
    $condition_and->condition('node_field_data.type', 'vopros_otvet', '!=');
    $condition_and->condition('node_field_data.type', 'service_price', '!=');
    $this->searchInBase($field_tables, $condition_and);
  }

  private function searchByTerms() {
    $items = array_keys(Vocabulary::loadMultiple());
    $field_tables = ['taxonomy_term_field_data' => []];

    $this->collectFieldTables('taxonomy_term', $items, 'tid', $field_tables);
    $this->collectFieldTables('node', $items, 'nid', $field_tables);
    $condition_and = new Condition('AND');
    $condition_and->condition('taxonomy_term_field_data.vid', 'service_type2', '!=');
    $this->searchInBase($field_tables, $condition_and);
  }

  private function collectFieldTables($type, $item_list_of_type, $primary_key, &$field_tables = []) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $entity_field_manager = \Drupal::service('entity_field.manager');
    $entity_storage = $entity_type_manager->getStorage($type);
    $field_storage_definitions = $entity_field_manager->getFieldStorageDefinitions($type);
    $table_mapping = $entity_storage->getTableMapping($field_storage_definitions);
    foreach ($item_list_of_type as $item) {
      $fields = \Drupal::service('entity_field.manager')
        ->getFieldDefinitions($type, $item);
      foreach ($fields as $field_name => $field) {
        $field_type = $field->getType();
        if (strpos($field_type, 'text') !== 0 && strpos($field_type, 'string') !== 0) {
          continue;
        }
        $table = $table_mapping->getFieldTableName($field_name);
        $column_names = $table_mapping->getColumnNames($field_name);
        $all_columns = $table_mapping->getAllColumns($table);
        if (in_array($primary_key, $all_columns)) {
          $key_field = $primary_key;
        }
        elseif (in_array('entity_id', $all_columns)) {
          $key_field = 'entity_id';
        }
        else {
          continue;
        }
        $field_tables[$table][$column_names['value']] = [
          'search' => $column_names['value'],
          'id' => $key_field,
        ];
      }
    }
  }

  private function searchInBase($field_tables, $additionalCondition = null) {
    //TODO сделать возможным поиск по части запроса (разбивка на слова)
    reset($field_tables);
    $first_table = key($field_tables);
    $first_table_fields = array_shift($field_tables);
    $first_table_field_select = [];
    foreach ($first_table_fields as $first_table_field) {
      foreach ($first_table_field as $first_table_field_part) {
        $first_table_field_select[$first_table_field_part] = $first_table_field_part;
      }
    }

    /** @var $connection \Drupal\Core\Database\Connection */
    $connection = \Drupal::service('database');
    $connection->query("SET SESSION sql_mode = ''")->execute();
    $query = $connection->select($first_table);
    $query->fields($first_table, $first_table_field_select);

    $condition_or = new Condition('OR');
    foreach ($first_table_fields as $first_table_field) {
      $condition_or->condition($first_table . '.' . $first_table_field['search'], '[[:<:]]' . $connection->escapeLike($this->search_string) . '[[:>:]]', 'RLIKE');
    }

    foreach ($field_tables as $field_table => $field_table_fields) {
      foreach ($field_table_fields as $field_table_field_parts) {
        $query->leftJoin($field_table, NULL, $first_table . '.' . array_values($first_table_fields)[0]['id'] . ' = ' . $field_table . '.' . $field_table_field_parts['id']);
        $query->fields($field_table, [$field_table_field_parts['search']]);
        $condition_or->condition($field_table . '.' . $field_table_field_parts['search'], '[[:<:]]' . $connection->escapeLike($this->search_string) . '[[:>:]]', 'RLIKE');
      }
    }

    $query->condition($condition_or);

    if ($additionalCondition !== null) {
      $query->condition($additionalCondition);
    }
    $query->groupBy($first_table . '.' . array_values($first_table_fields)[0]['id']);

    $results = $query->execute();
    $results = $results->fetchAll();
    if ($results) {
      $this->search_result = array_merge($this->search_result, $results);
    }
  }

  private function handleSearchResult() {
    foreach ($this->search_result as &$search_result_item) {
      $search_result_item = array_filter((array) $search_result_item);
      foreach ($search_result_item as $search_result_item_field_value) {
        $text = strip_tags($search_result_item_field_value);
        $pos = mb_stripos($text, $this->search_string, 0, 'UTF-8');
        if ($pos !== FALSE) {
          $text = $this->truncateSearchResult($text, $pos);
          $search_result_item['search_description'] = $this->highlightingSearchResult($text);
          $title = $search_result_item['name'] ?? $search_result_item['title'];
          $search_result_item['title'] = $this->highlightingSearchResult($title);
          $search_result_item['link'] = $search_result_item['field_link_in_list_value'] ?? '';
        }
      }
    }
  }

  private function highlightingSearchResult($text) {
    $pos = mb_stripos($text, $this->search_string, 0, 'UTF-8');
    if ($pos !== false) {
      $first_part = mb_substr($text, 0, $pos, 'UTF-8');
      $search_string_in_text = mb_substr($text, $pos, mb_strlen($this->search_string, 'UTF-8'), 'UTF-8');
      $second_part = mb_substr($text, $pos + mb_strlen($this->search_string, 'UTF-8'), NULL, 'UTF-8');
      return $first_part . '<b>' . $search_string_in_text . '</b>' . $second_part;
    }
    return $text;
  }

  private function truncateSearchResult($text, $pos) {
    $text_len = mb_strlen($text, 'UTF-8');
    if ($text_len > 250) {
      $search_string_len = mb_strlen($this->search_string, 'UTF-8');
      $search_string = mb_substr($text, $pos, $search_string_len, 'UTF-8');
      if ($pos > 125) {
        $first_part = mb_substr($text, $pos - 125, 125, 'UTF-8');
        $first_part = $this->trimFirstWord($first_part);
        $first_part = '...' . preg_replace('/^([ \-–])?([ \-–])?([ \-–])?(.*?)/iuS', '$4', $first_part);
        $second_part = mb_substr($text, $pos + $search_string_len, 125 - $search_string_len, 'UTF-8');
      }
      else {
        $first_part = mb_substr($text, 0, $pos, 'UTF-8');
        $second_part = mb_substr($text, $pos + $search_string_len, 250 - ($pos + $search_string_len), 'UTF-8');
      }
      if (!$this->endsWith($text, $second_part)) {
        $second_part = $this->trimLastWord($second_part);
        $second_part = preg_replace('/(.*?)([ \-–])?([ \-–])?([ \-–])?$/iuS', '$1', $second_part) . '...';
      }
      $text = $first_part . $search_string . $second_part;
    }
    return $text;
  }

  private function trimLastWord($text) {
    $words = $this->getWords($text);
    $last_word = array_pop($words);
    return mb_substr($text, 0, -mb_strlen($last_word, 'UTF-8') - 1, 'UTF-8');
  }

  private function trimFirstWord($text) {
    $words = $this->getWords($text);
    $first_word = array_shift($words);
    return mb_substr($text, mb_strlen($first_word, 'UTF-8') + 1, NULL, 'UTF-8');
  }

  private function getWords($text) {
    return str_word_count($text, 1, 'АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя');
  }

  private function endsWith($string, $endString) {
    $len = strlen($endString);
    if ($len == 0) {
      return TRUE;
    }
    return (substr($string, -$len) === $endString);
  }
}