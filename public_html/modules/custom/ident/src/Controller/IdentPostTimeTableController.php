<?php
namespace Drupal\ident\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Zend\Diactoros\Request as ZendRequest;

class IdentPostTimeTableController extends ControllerBase {

  public function execute(Request $request) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $response = new Response();
    $response->headers->set('Content-Type', 'text/plain');

    // Запишем все заголовки в лог
    $headers = $request->headers->all();
    \Drupal::logger('ident')->info(json_encode($headers));

    // Запишем в лог всё тело запроса
    $content = $request->getContent();
    \Drupal::logger('ident')->info($content);

    if ($request->getMethod() !== 'POST') {
      $response->setStatusCode(405);
      $response->headers->set('Allow', 'POST');
      $response->setContent('Разрешён только POST запрос');
      return $response;
    }

    // Меняем регистр ключей массива заголовков
    $headers = array_change_key_case($headers, CASE_LOWER);

    if (!isset($headers['ident-integration-key'])) {
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

    if ($ident_integration_key !== 'ident-integration-key-value') {
      $response->setStatusCode(401);
      $response->setContent('Неправильный ключ доступа');
      return $response;
    }

    // Пытаемся разобрать тело запроса, должен быть валидный json
    if (
      !is_string($content)
      || !is_array(json_decode($content, TRUE))
      || !(json_last_error() == JSON_ERROR_NONE))
    {
      $response->setStatusCode(452);
      $response->setContent('Был принят невалидный json');
      return $response;
    }

    // Отправляем принятые данные дальше на сохранение
    /** @var $doctors \Drupal\ident\Doctors */
    $doctors = \Drupal::service('ident.doctors');
    $handlerResponse = $doctors->updateTime($content);

    if ($handlerResponse != 'OK') {
      $response->setStatusCode(500);
      $response->setContent($handlerResponse);
      return $response;
    }

    $response->setStatusCode(200);
    $response->setContent('OK');

    return $response;
  }
}
