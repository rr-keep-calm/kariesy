<?php
namespace Drupal\ident\Controller;

use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Zend\Diactoros\Request as ZendRequest;

class IdentGetTicketsController extends ControllerBase {

  public function execute(Request $request) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $response = new Response();
    $response->headers->set('Content-Type', 'text/plain');

    if ($request->getMethod() !== 'GET') {
      $response->setStatusCode(405);
      $response->headers->set('Allow', 'GET');
      $response->setContent('Разрешён только GET запрос');
      return $response;
    }

    $headers = $request->headers->all();
    // Меняем регистр ключей массива заголовков
    $headers = array_change_key_case($headers, CASE_LOWER);

    /*if (!isset($headers['ident-integration-key'])) {
      $response->setStatusCode(401);
      $response->setContent('Отсутствует заголовок ключа доступа');
      return $response;
    }

    // Проверяем идентификационный ключ
    if (is_array($headers['ident-integration-key'])) {
      $ident_integration_key = $headers['ident-integration-key'][0];
    }
    else {
      $ident_integration_key = $headers['ident-integration-key'];
    }

    if ($ident_integration_key !== 'q3MfBqjTAYrk') {
      $response->setStatusCode(401);
      $response->setContent('Неправильный ключ доступа');
      return $response;
    }*/

    $params = [
      'dateTimeFrom' => $request->get('dateTimeFrom'),
      'dateTimeTo' => $request->get('dateTimeTo'),
      'limit' => $request->get('limit'),
      'offset' => $request->get('offset'),
    ];

    if (!$params['dateTimeFrom']) {
      $response->setStatusCode(400);
      $response->setContent('Не передан обязательный параметр "dateTimeFrom"');
      return $response;
    }

    if (!$params['dateTimeTo']) {
      $response->setStatusCode(400);
      $response->setContent('Не передан обязательный параметр "dateTimeTo"');
      return $response;
    }

    /** @var $form_orders \Drupal\ident\FormOrders */
    $form_orders = \Drupal::service('ident.form_orders');
    $handlerResponse = $form_orders->getFormOrders($params);

    if ($handlerResponse['status'] !== 'OK') {
      $response->setStatusCode(500);
      $response->setContent($handlerResponse['error']);
      return $response;
    }

    $response->setStatusCode(200);
    $response->setContent(json_encode($handlerResponse['form_orders']));

    return $response;
  }
}
