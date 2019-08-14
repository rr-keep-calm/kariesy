<?php
namespace Drupal\ident;

class Doctors {

  /**
   * @param string $content JSON данные по расписанию для каждого доктора
   *
   * @return string ответ на попытку обработки данных
   */
  public function updateTime($content): string
  {
    // Получаем всех докторов представленных на сайте
    $nids = \Drupal::entityQuery('node')->condition('type','doktor')->execute();
    $doctors_tmp =  \Drupal\node\Entity\Node::loadMultiple($nids);

    $doctors = [];
    foreach ($doctors_tmp as $id => $doctor) {
      $ident_id = $doctor->get('field_ident_id')->value;
      if ($ident_id) {
        $doctors[$ident_id] = $doctor;
      }
    }

    $doctors_slots = [];
    $content = json_decode($content, true);
    foreach ($content['Intervals'] as $interval) {
      if (!isset($doctors[$interval['DoctorId']])) {
        $name = $this->searchDoctor($content['Doctors'], $interval['DoctorId']);
        \Drupal::logger('ident')->warning('Доктор "' . $name . '" не представлен(а) на сайте');
      }elseif (!$doctors[$interval['DoctorId']]->isPublished()) {
        $name = $this->searchDoctor($content['Doctors'], $interval['DoctorId']);
        \Drupal::logger('ident')->warning('Доктор "' . $name . '" не активен');
      } else {
        $doctors_slots[$interval['DoctorId']][] = [
          'StartDateTime' => $interval['StartDateTime'],
          'LengthInMinutes' => $interval['LengthInMinutes'],
          'IsBusy' => $interval['IsBusy']
        ];
      }
    }

    // Записываем данные слотов по каждому доктору
    foreach ($doctors_slots as $doctor_id => $doctor_slots) {
      $doctors[$doctor_id]->field_ident_slots->value = json_encode($doctor_slots);
      $doctors[$doctor_id]->save();
    }

    return 'OK';
  }

  protected function searchDoctor($doctors, $ident_id)
  {
    foreach ($doctors as $request_data_doctors) {
      if ((int)$request_data_doctors['Id'] === (int)$ident_id) {
        return $request_data_doctors['Name'];
      }
    }
  }
}
