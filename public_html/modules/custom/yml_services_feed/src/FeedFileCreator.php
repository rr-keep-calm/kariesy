<?php
namespace Drupal\yml_services_feed;

use Drupal\taxonomy\Entity\Term;

class FeedFileCreator {

  public function create() {
    $result = [];

    // Получаем активные первые уровни словаря "Услуга"
    $services = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('service', 0, 1, true);

    // проходим по всем услугам
    foreach ($services as $service) {
      // Если услуга не опубликаована или не имеет цены, то она не попадает в фид
      if (
        !$service->isPublished()
        || !$service->field_price->value
      ) {
        continue;
      }

      // собираем типы услуг (категории)
      $service_type_tids = $service->get('field_service_type');

      // Услуги не отнесённые к типу услуги в фид не попадают
      if (!$service_type_tids->count()) {
        continue;
      }

      // В категорию попадает первый тип услуги к которому отнесена цена
      $service_type_tid = $service_type_tids->first()->target_id;
      $service_type_term = Term::load($service_type_tid);
      $result['categories'][$service_type_tid] = $service_type_term->getName();

      $service_variables = [
        'url' => $service->toUrl('canonical', ['absolute' => TRUE])->toString(),
        'price' => $service->field_price->value,
        'category_id' => $service_type_tid,
        'name' => $service->getName(),
        'description' => $service->getDescription()
      ];

      if (!$service->get('field_oblozhka')->isEmpty()) {
        $service_variables['image_url'] = file_create_url($service->field_oblozhka->entity->getFileUri());
      }

      $result['services'][$service->id()] = $service_variables;
    }

    return $result;
  }
}
