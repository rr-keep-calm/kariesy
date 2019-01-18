<?php
namespace Drupal\keepCalmTwigExtensions\TwigExtension;


class KeepCalmTwigExtensions extends \Twig_Extension {
  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('clearNotNum', array($this, 'clearNotNum')),
    ];
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'keepCalmTwigExtensions.twig_extension';
  }

  /**
   * Replaces all numbers from the string.
   */
  public static function clearNotNum($string) {
    return preg_replace('/\D/', '', $string);
  }
}