<?php
namespace Drupal\ident;

class Doctors {

  /**
   * Интервал выремени в минутах при записи на приём с формы
   */
  const MINIMUM_SLOT_TIME_INTERVAL = 15;

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

      // Очищаем поле "Занятые слоты"
      $doctors[$doctor_id]->field_busy_slots_from_form->value = '';
      $doctors[$doctor_id]->save();
    }

    return 'OK';
  }

  /**
   * Получаем слоты доктора
   *
   * @param int $nid Идентификатор ноды доктора
   *
   * @return string ответ на попытку получения слотов доктора
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getSlots(int $nid): string
  {
    $doctor = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    $ident_slots = $doctor->get('field_ident_slots')->value;

    // Получаем занятые слоты
    $busy_slots_from_form = $doctor->get('field_busy_slots_from_form')->value;
    if ($busy_slots_from_form) {
      if ($ident_slots) {
        $busy_slots_from_form = json_decode($busy_slots_from_form, TRUE);
        // Если есть занятые слоты, то актуализируем слоты из IDENT
        $ident_slots = json_decode($ident_slots, TRUE);
        usort($ident_slots, [get_class($this), 'sortByDate']);
        foreach ($busy_slots_from_form as $busy_slot_from_form) {
          $this->actualizationSlots($ident_slots, $busy_slot_from_form);
        }
        $ident_slots = json_encode($ident_slots);
      }
      else {
        $ident_slots = $busy_slots_from_form;
      }
    }
    return $ident_slots ?? '';
  }

  protected function searchDoctor($doctors, $ident_id)
  {
    foreach ($doctors as $request_data_doctors) {
      if ((int)$request_data_doctors['Id'] === (int)$ident_id) {
        return $request_data_doctors['Name'];
      }
    }
  }

  protected function actualizationSlots(&$ident_slots, $busy_slot_from_form)
  {
    $busy_date_time_from_form = explode('T', $busy_slot_from_form['StartDateTime']);
    $busy_time_from_form_parts = explode(':', $busy_date_time_from_form[1]);
    $busy_slot_from_form_parts_for_init_object = explode('+', $busy_slot_from_form['StartDateTime']);
    $busy_slot_from_form_object = new \DateTime($busy_slot_from_form_parts_for_init_object[0]);
    foreach ($ident_slots as $key => $ident_slot) {
      if ($ident_slot['IsBusy'] === TRUE || $ident_slot['IsBusy'] === 'true') {
        continue;
      }
      $slot_date_time = explode('+', $ident_slot['StartDateTime']);
      $slot_date_time_start = new \DateTime($slot_date_time[0]);
      $slot_date_time_end = clone $slot_date_time_start;
      $slot_date_time_end->add(new \DateInterval('PT' . $ident_slot['LengthInMinutes'] . 'M'));
      if (
        $slot_date_time_start <= $busy_slot_from_form_object
        && $slot_date_time_end > $busy_slot_from_form_object
      ) {
        /** @noinspection PhpUndefinedVariableInspection */
        $handle_slot_date_time = explode('T', $ident_slots[$key]['StartDateTime']);
        $handle_slot_time = explode(':', $handle_slot_date_time[1]);
        $hour = (int)$handle_slot_time[0];
        $hour_mark = $handle_slot_time[0];

        $min = (int)$handle_slot_time[1];
        $min_mark = $handle_slot_time[1];
        for ($i = 0; $i < $ident_slots[$key]['LengthInMinutes']/self::MINIMUM_SLOT_TIME_INTERVAL; $i++) {
          if ($i !== 0) {
            $min += self::MINIMUM_SLOT_TIME_INTERVAL;
            $min_mark = (string)$min;
          }
          if ($min < 10 && $min_mark !== '00' && $min_mark !== '05') {
            $min_mark = '0' . $min_mark;
          }

          if ($min === 60) {
            $hour++;
            $hour_mark = (string)$hour;
            if ($hour < 10) {
              $hour_mark = '0' . $hour_mark;
            }
            $min = 0;
            $min_mark = '00';
          }
          $ident_slots[] = [
            'StartDateTime' => $handle_slot_date_time[0] . 'T' . $hour_mark . ':' . $min_mark . ':00+03',
            'LengthInMinutes' => self::MINIMUM_SLOT_TIME_INTERVAL,
            'IsBusy' => $this->checkBusy(
              $hour,
              $min,
              (int) $busy_time_from_form_parts[0],
              (int) $busy_time_from_form_parts[1],
              (int) $busy_slot_from_form['LengthInMinutes']
            ),
          ];
        }
        unset($ident_slots[$key]);
        usort($ident_slots, [get_class($this), 'sortByDate']);
        break;
      }
    }
  }

  protected function sortByDate($a, $b)
  {
    $date_a = new \DateTime($a['StartDateTime']);
    $date_b = new \DateTime($b['StartDateTime']);
    return $date_a <=> $date_b;
  }

  protected function checkBusy($slot_hour, $slot_min, $busy_hour, $busy_min, $busy_length_in_minutes)
  {
    return $slot_hour === $busy_hour
      && (
        $slot_min === $busy_min
        || (
          $slot_min > $busy_min
          && $slot_min < $busy_min + $busy_length_in_minutes
        )
      );
  }
}
