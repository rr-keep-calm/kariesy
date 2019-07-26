<?php

namespace Drupal\x_frame_options_control\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * An event subscriber to remove the X-Frame-Options header.
 */
class RemoveXFrameOptionsSubscriber implements EventSubscriberInterface {

  /**
   * Remove the X-Frame-Options header.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function RemoveXFrameOptions(FilterResponseEvent $event) {
    $previousUrl = \Drupal::request()->server->get('HTTP_REFERER');

    if (!$previousUrl || preg_match('/^https?:\/\/([^\/]+\.)?(kariesy\.net|webvisor\.com)\//', $previousUrl)) {
      $response = $event->getResponse();
      $response->headers->remove('X-Frame-Options');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['RemoveXFrameOptions', -10];
    return $events;
  }

}
