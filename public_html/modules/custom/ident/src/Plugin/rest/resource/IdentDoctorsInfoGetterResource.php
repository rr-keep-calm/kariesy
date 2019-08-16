<?php

namespace Drupal\ident\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to create new article.
 *
 * @RestResource(
 *   id = "ident_doctors_info_getter",
 *   label = @Translation("Ident doctors info getter"),
 *   uri_paths = {
 *     "canonical" = "/get/doctor-slots",
 *   }
 * )
 */
class IdentDoctorsInfoGetterResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new CreateArticleResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('ck_form_handler'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   */
  public function get() {
    $query = \Drupal::request()->query;
    $response = [];
    if ($query->has('nid')) {
      /** @var $doctors \Drupal\ident\Doctors */
      $doctors = \Drupal::service('ident.doctors');
      $doctorSlotsResponse = $doctors->getSlots($query->get('nid'));
      return new ResourceResponse($doctorSlotsResponse);
    }
    else {
      return new ResourceResponse('Required parameter nid is not set.', 400);
    }
  }
}
