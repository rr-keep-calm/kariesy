<?php
namespace Drupal\ident;

class FormOrders {

  /**
   * @param array $params Параметры запроса для получения списка заявок
   *
   * @return array ответ на попытку получения заявок
   */
  public function getFormOrders($params): string
  {
    $form_orders = [];
    $status = 'OK';
    $error = '';
    // Получаем все не опубликованные заявки.
    // Опубликованные заявки считаются переданными
    $nids = \Drupal::entityQuery('node')
      ->condition('type','doktor')
      ->condition('status', 0)
      ->execute();
    $form_orders_tmp =  \Drupal\node\Entity\Node::loadMultiple($nids);
    $a = 1;

//    $doctors = [];
//    foreach ($doctors_tmp as $id => $doctor) {
//      $ident_id = $doctor->get('field_ident_id')->value;
//      if ($ident_id) {
//        $doctors[$ident_id] = $doctor;
//      }
//    }
//
//    $doctors_slots = [];
//    $content = json_decode($content, true);
//    foreach ($content['Intervals'] as $interval) {
//      if (!isset($doctors[$interval['DoctorId']])) {
//        $name = $this->searchDoctor($content['Doctors'], $interval['DoctorId']);
//        \Drupal::logger('ident')->warning('Доктор "' . $name . '" не представлен(а) на сайте');
//      }elseif (!$doctors[$interval['DoctorId']]->isPublished()) {
//        $name = $this->searchDoctor($content['Doctors'], $interval['DoctorId']);
//        \Drupal::logger('ident')->warning('Доктор "' . $name . '" не активен');
//      } else {
//        $doctors_slots[$interval['DoctorId']][] = [
//          'StartDateTime' => $interval['StartDateTime'],
//          'LengthInMinutes' => $interval['LengthInMinutes'],
//          'IsBusy' => $interval['IsBusy']
//        ];
//      }
//    }
//
//    // Записываем данные слотов по каждому доктору
//    foreach ($doctors_slots as $doctor_id => $doctor_slots) {
//      $doctors[$doctor_id]->field_ident_slots->value = json_encode($doctor_slots);
//      $doctors[$doctor_id]->save();
//    }

    return [
      'status' => $status,
      'form_orders' => $form_orders,
      'error' => $error
    ];
  }
}
