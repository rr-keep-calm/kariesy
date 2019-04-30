<?php

namespace Drupal\ck_form_handler\StackMiddleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Try to get user source
 */
class CkFormHandlerGetSource implements HttpKernelInterface {

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Creates a HTTP middleware handler.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $kernel
   *   The HTTP kernel.
   */
  public function __construct(HttpKernelInterface $kernel) {
    $this->httpKernel = $kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    if (!isset($_COOKIE['ck_source'])) {
      $source = 'Прямой заход';
      // Определение источника по переходу с контекстной рекламы
      if (trim($_GET['utm_source']) !== '') {
        switch ($_GET['utm_source']) {
          case 'yandex.search':
            $source = 'Яндекс.Директ [Поиск]';
            break;
          case 'yandex.context':
            $source = 'Яндекс.Директ [РСЯ]';
            break;
          case 'google':
            $source = 'Google Ads';
            break;
          case 'instagram':
            $source = 'Инстаграм';
            break;
          case 'vk':
            $source = 'ВК';
            break;
          case 'facebook':
            $source = 'Facebook';
            break;
          case 'mytarget':
            $source = 'myTarget';
            break;
        }
      }
      elseif (trim($_SERVER['HTTP_REFERER']) !== '') {
        if (preg_match('/yandex.ru\/clck/', $_SERVER['HTTP_REFERER'])) {
          $source = 'Поиск Яндекса';
        }
        elseif (preg_match('/google.com/', $_SERVER['HTTP_REFERER'])) {
          $source = 'Поиск Google';
        }
        elseif (preg_match('/go.mail.ru/', $_SERVER['HTTP_REFERER'])) {
          $source = 'Поиск Mail.ru';
        }
        elseif (preg_match('/nova.rambler.ru/', $_SERVER['HTTP_REFERER'])) {
          $source = 'Поиск Рамблер';
        }
        elseif (preg_match('/instagram.com/', $_SERVER['HTTP_REFERER'])) {
          $source = 'Инстаграм';
        }
        elseif (preg_match('/vk.com/', $_SERVER['HTTP_REFERER'])) {
          $source = 'ВК';
        }
        elseif (preg_match('/facebook.com/', $_SERVER['HTTP_REFERER'])) {
          $source = 'Facebook';
        }
        else {
          $ref = parse_url($_SERVER['HTTP_REFERER']);
          $source = 'Переход с сайта "' . $ref['host'] . '"';
        }
      }
      $host = $request->getHost();
      setcookie('ck_source', $source, time() + 31536000, '/', $host);
    }
    return $this->httpKernel->handle($request, $type, $catch);
  }
}