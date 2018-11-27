<?php

namespace Drupal\tilda_export\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "tilda_rest_resource",
 *   label = @Translation("Tilda rest resource"),
 *   uri_paths = {
 *     "canonical" = "/export_check"
 *   }
 * )
 */
class TildaRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new TildaRestResource object.
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
      $container->get('logger.factory')->get('tilda_export'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   * @throws \Exception
   *   Throws exception expected.
   */
  public function get() {
    $query = \Drupal::request()->query;
    $response = ['ok'];
    // TODO далее для внесения в базу нужно использовать безопасные запросы и аутентификацию. хотя бы базовую
    // Првоеряем наличие всех параметров для внесения в базу
    if (!$query->has('projectid') ||
      !$query->has('pageid') ||
      !$query->has('published') ||
      !$query->has('publickey')
    ) {
      return new ModifiedResourceResponse('Не указан идентификатор проекта!', 400);
    }

    // Првоеряем что это точно нужный проект (читай сайт)
    // TODO В дальнейшем брать идентификатор проекта из настроек модуля
    if ($query->get('projectid') != '931691') {
      return new ModifiedResourceResponse('Этот проект не может обработать запрос, обратитесь к нужному проекту!', 400);
    }

    // Записывем информацию о странице которая в дальнейшем будет забираться из
    // Тильды при наступлении крона
    $insertQuery = \Drupal::database()->insert('tilda_need_export');
    $insertQuery->fields([
      'projectid' => $query->get('projectid'),
      'pageid' => $query->get('pageid'),
      'published' => $query->get('published'),
      'publickey' => $query->get('publickey')
    ]);
    $newID = $insertQuery->execute();
    if (!$newID) {
      return new ModifiedResourceResponse('Не удалось оповестить сайт о надобности экспорта контента из Тильды!', 400);
    }
    return new ModifiedResourceResponse($response, 200);
  }

}
