<?php

namespace Drupal\services_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ServicesExportController extends ControllerBase {

  public function getFile(Request $request): Response {
    \Drupal::service('page_cache_kill_switch')->trigger();

    // получаем необходимые данные в xls представлении
    /** @var $xls \Drupal\services_export\FileGenerate */
    $xls = \Drupal::service('services_export.file_generate');
    $xls_content = $xls->generate();

    $response = new Response();
    $response->headers->set('Content-Type', 'application/vnd.ms-excel');
    $response->setContent($xls_content);
    $response->setStatusCode(200);

    return $response;
  }
}
