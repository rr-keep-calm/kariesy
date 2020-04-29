<?php
namespace Drupal\working_hours_settings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class WorkingHoursSettingsController extends ControllerBase {

  public function execute(Request $request) {
    $response = new Response();
    $response->headers->set('Content-Type', 'text/plain');

    if ($request->getMethod() !== 'GET') {
      $response->setStatusCode(405);
      $response->headers->set('Allow', 'GET');
      $response->setContent('Разрешён только GET запрос');
      return $response;
    }

    $response->setStatusCode(200);
    $response->headers->set('Content-Type', 'application/json');
    $response->setContent(json_encode(\Drupal::service('working_hours_settings.get_working_hours_strings')->getWorkingHoursStrings()));

    return $response;
  }
}
