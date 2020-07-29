<?php
/**
 * @file
 * Contains \Drupal\dental_formula_field\Element\DentalFormula.
 */

namespace Drupal\dental_formula_field\Element;

use Drupal;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an example element.
 *
 * @FormElement("dental_formula")
 */
class DentalFormula extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#theme' => 'dental_formula',
      '#label' => 'Зубная формула',
      '#description' => 'Зубная формула',
      '#pre_render' => [
        [$class, 'preRenderDentalFormula'],
      ],
    ];
  }

  public static function preRenderDentalFormula($element) {
    $element['#attributes']['type'] = 'hidden';
    $element['moduleUrl'] = Drupal::moduleHandler()
      ->getModule('dental_formula_field')
      ->getPath();
    return $element;
  }

  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    $toSave = [];

    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-11');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-12');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-13');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-14');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-15');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-16');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-17');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-18');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-21');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-22');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-23');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-24');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-25');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-26');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-27');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-28');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-31');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-32');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-33');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-34');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-35');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-36');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-37');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-38');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-41');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-42');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-43');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-44');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-45');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-46');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-47');
    $toSave[] = (int) (bool) Drupal::request()->request->get('tooth-48');

    if ($toSave) {
      return implode('', $toSave);
    }
    return $element['#default_value'];
  }
}
