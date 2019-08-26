<?php

namespace Drupal\ident\EventSubscriber;

use \Drupal\node\Entity\Node;
use Drupal\ck_form_handler\Event\СkFormHandlerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Подписчик на события
 */
class IdentSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      СkFormHandlerEvent::APPOINTMENT_ORDER_SAVE => ['orderSave']
    ];
  }

  /**
   * Метод для обработки события записи на приём после сохранения в базе
   */
  public function orderSave(СkFormHandlerEvent $event) {
    $formData = $event->getFormData();
    if ($formData['doctor_nid']) {
      /** @var \Drupal\ident\Doctors $doctor_service*/
      $doctor_service = \Drupal::service('ident.doctors');

      $doctorNode = Node::load($formData['doctor_nid']);
      $busy_slots_from_form = $doctorNode->get('field_busy_slots_from_form')->value;
      if (!$busy_slots_from_form) {
        $busy_slots_from_form = [];
      }
      else {
        $busy_slots_from_form = json_decode($busy_slots_from_form, TRUE);
      }
      $dateParts = explode('.', $formData['date']);
      $busy_slots_from_form[] = [
        'StartDateTime' => $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0] . 'T' . $formData['time'] . ':00+03',
        'LengthInMinutes' => $doctor_service::MINIMUM_SLOT_TIME_INTERVAL,
        //TODO выяснить сколько времени выставлять занятым при записи чере зформу
        'IsBusy' => TRUE
      ];
      $doctorNode->field_busy_slots_from_form->value = json_encode($busy_slots_from_form);
      $doctorNode->save();
    }
  }
}
