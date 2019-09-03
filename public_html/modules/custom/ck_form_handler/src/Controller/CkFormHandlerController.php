<?php

namespace Drupal\ck_form_handler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CkFormHandlerController extends ControllerBase {

  public function execute(Request $request): Response {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');
    $form_data = json_decode($request->getContent(), TRUE);

    if ($request->getMethod() !== 'POST') {
      $response->setStatusCode(405);
      $response->headers->set('Allow', 'POST');
      $response->setContent(json_encode(['text' => 'Разрешён только POST запрос']));
      return $response;
    }

    /** @var $form_handler_helper \Drupal\ck_form_handler\FormHandlerHelper */
    $name = $request->get('name');
    $form_handler_helper = \Drupal::service('ck_form_handler.form_handler_helper');
    $form_handler_helper->setFormData($form_data);
    $form_handler_helper->sendEmail();
    $text = $form_handler_helper->getResponse();
    $response->setContent(json_encode(['text' => $text]));
    $response->setStatusCode(200);

    return $response;
  }
}
