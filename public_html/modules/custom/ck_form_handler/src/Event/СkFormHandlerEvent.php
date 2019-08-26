<?php

namespace Drupal\ck_form_handler\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Событие наступает в момент обработки формы записи на приём
 */
class СkFormHandlerEvent extends Event {

  /**
   * Вызывается после сохранения заказа в базе
   */
  const APPOINTMENT_ORDER_SAVE = 'ck_form_handler.appointment_order_save';

  /**
   * Данные от формы.
   */
  protected $formData;

  /**
   * DummyFrontpageEvent constructor.
   */
  public function __construct($formData) {
    $this->formData = $formData;
  }

  /**
   * @return mixed
   */
  public function getFormData() {
    return $this->formData;
  }

}
