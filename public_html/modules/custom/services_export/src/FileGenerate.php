<?php

namespace Drupal\services_export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FileGenerate {

  public function generate() {
    $config = \Drupal::config('services_export.adminsettings');
    $service_types_exclude = $config->get('service_type_exclude');
    array_walk($service_types_exclude, function (&$item) {
      $item = $item['target_id'];
    });
    $service_exclude = $config->get('service_exclude');
    array_walk($service_exclude, function (&$item) {
      $item = $item['target_id'];
    });

    // Получаем все типы услуг
    $service_types_data = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('service_type');
    $service_types = [];
    foreach ($service_types_data as $service_type) {
      if (in_array($service_type->tid, $service_types_exclude)) {
        continue;
      }
      $service_types[$service_type->tid] = $service_type->name;
    }

    $services_data = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('service', 0, NULL, TRUE);
    $services = [];
    foreach ($services_data as $service) {
      if (in_array($service->id(), $service_exclude)) {
        continue;
      }

      // Тип услуги
      $service_type = 'Услуга без категории';
      $service_type_value = $service->get('field_service_type')->getValue();
      if ($service_type_value) {
        if (!isset($service_types[reset($service_type_value)['target_id']])) {
          continue;
        }
        $service_type = $service_types[reset($service_type_value)['target_id']];
      }

      // Описание
      $description_value = $service->get('field_opisanie_v_spiske_uslug')->getValue();
      $description = '';
      if ($description_value) {
        $description = strip_tags(reset($description_value)['value']);
      }

      // Цена
      $price_value = $service->get('field_price')->getValue();
      $price = '';
      if ($price_value) {
        $price = strip_tags(reset($price_value)['value']);
      }

      // Пиктограмма
      $piktogramma_value = $service->get('field_piktogramma')->getValue();
      $piktogramma = '';
      if ($piktogramma_value) {
        $file = \Drupal\file\Entity\File::load(reset($piktogramma_value)['target_id']);
        $uri = $file->getFileUri();
        $piktogramma = \Drupal\Core\Url::fromUri(file_create_url($uri))
          ->toString();
      }

      // Популярный товар
      $popular_value = $service->get('field_popular')->getValue();
      $popular = 'Нет';
      if ($popular_value) {
        $popular = reset($popular_value)['value'] ? 'Да' : 'Нет';
      }
      $services[$service->id()] = [
        $service_type,
        $service->getName(),
        $description,
        $price,
        $piktogramma,
        $popular,
        'Да',
      ];
    }

    usort($services, function ($a, $b) {
      return $a[0] <=> $b[0];
    });
    $path = \Drupal::service('file_system')->realpath(
      drupal_get_path('module', 'services_export')
    );

    require $path . '/vendor/autoload.php';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Строим шапку
    $sheet->setCellValue('A1', 'Категория');
    $sheet->setCellValue('B1', 'Название');
    $sheet->setCellValue('C1', 'Описание');
    $sheet->setCellValue('D1', 'Цена');
    $sheet->setCellValue('E1', 'Фото');
    $sheet->setCellValue('F1', 'Популярный товар');
    $sheet->setCellValue('G1', 'В наличии');

    // Заполняем табллицу данными
    $i = 2;
    foreach ($services as $service_item) {
      $sheet->setCellValue('A' . $i, $service_item[0]);
      $sheet->setCellValue('B' . $i, $service_item[1]);
      $sheet->setCellValue('C' . $i, $service_item[2]);
      $sheet->setCellValue('D' . $i, $service_item[3]);
      $sheet->setCellValue('E' . $i, $service_item[4]);
      $sheet->setCellValue('F' . $i, $service_item[5]);
      $sheet->setCellValue('G' . $i, $service_item[6]);
      $i++;
    }

    $writer = new Xlsx($spreadsheet);
    ob_start();
    $writer->save('php://output');
    return ob_get_clean();
  }
}
