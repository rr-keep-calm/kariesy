<?php

namespace Drupal\entity_based_settings_helper;


use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeListBuilder;

class EntityBasedSettingsHelperNodeListBuilder extends NodeListBuilder {

  public function buildRow(EntityInterface $entity) {
    return parent::buildRow($entity);
  }
}
