<?php
namespace Drupal\ident;

use \Drupal\node\Entity\Node;

class FormOrders {

  /**
   * @param array $params Параметры запроса для получения списка заявок
   *
   * @return array ответ на попытку получения заявок
   * @throws \Exception
   */
  public function getFormOrders($params): array
  {
    $form_orders = [];
    $status = 'OK';
    $error = '';
    $form_order_nids = [];
    // Получаем все не опубликованные заявки.
    // Опубликованные заявки считаются переданными
    $start = new \DateTime($params['dateTimeFrom']);
    $end = new \DateTime($params['dateTimeTo']);
    $nids = \Drupal::entityQuery('node')
      ->condition('type','form_order')
      ->condition('status', 0)
      ->condition('created', $start->getTimestamp(), '>=')
      ->condition('created', $end->getTimestamp(), '<=')
      ->execute();
    $form_orders_tmp =  \Drupal\node\Entity\Node::loadMultiple($nids);
    $form_orders_tmp = array_slice(
      $form_orders_tmp,
      $params['offset'],
      $params['limit'] ?? NULL
    );

    // Компануем заявки для отдачи
    foreach ($form_orders_tmp as $form_order) {
      $form_order_nids[] = $form_order->id();
      $compose_order_to_send = [
        'Id' => $form_order->id(),
        'DateAndTime' => date('c', $form_order->getCreatedTime()),
        'ClientPhone' => $form_order->get('field_phone')->value,
        'ClientFullName' => $form_order->get('field_form_order_fio')->value,
        'PlanStart' => $form_order->get('field_desired_date_and_time')->value,
        'Comment' => (string) $form_order->get('field_comment')->value,
      ];
      $doctor_id = $form_order->get('field_form_order_doctor')->target_id;
      if ($doctor_id) {
        $doctor = Node::load($doctor_id);
        $compose_order_to_send['DoctorName'] = $doctor->getTitle();
        $doctor_ident_id = $doctor->get('field_ident_id')->value;
        if ($doctor_ident_id) {
          $compose_order_to_send['DoctorId'] = $doctor_ident_id;
        }
      }
      $form_orders[] = (object)$compose_order_to_send;
    }

    return [
      'status' => $status,
      'form_orders' => $form_orders,
      'error' => $error,
      'form_order_nids' => $form_order_nids
    ];
  }

  public function setPublished($nids)
  {
    $orders = \Drupal\node\Entity\Node::loadMultiple($nids);
    foreach ($orders as $order) {
      $order->setPublished();
      $order->save();
    }
  }
}
