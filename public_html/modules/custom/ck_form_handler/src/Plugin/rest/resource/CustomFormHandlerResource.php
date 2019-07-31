<?php

namespace Drupal\ck_form_handler\Plugin\rest\resource;

use Drupal\ck_form_handler\FormHandlerHelper;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to create new article.
 *
 * @RestResource(
 *   id = "custom_form_handler",
 *   label = @Translation("Custom form handler"),
 *   uri_paths = {
 *     "create" = "/keep-calm-custom-form-handler",
 *   }
 * )
 */
class CustomFormHandlerResource extends ResourceBase {

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
   * Responds to POST requests.
   *
   * @param array $entityData Данные переданые на сервер
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   */
  public function post($entityData) {
    if (!$this->currentUser->hasPermission('restful post custom_form_handler')) {
      throw new AccessDeniedHttpException();
    }

    $form_handler_helper = \Drupal::service('ck_form_handler.form_handler_helper');
    $form_handler_helper->setFormData($entityData);
    $form_handler_helper->sendEmail();
    $response['text'] = $form_handler_helper->getResponse();

    return new ModifiedResourceResponse(json_encode($response), 200);
  }
}
