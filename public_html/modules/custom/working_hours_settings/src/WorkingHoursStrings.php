<?php

namespace Drupal\working_hours_settings;


class WorkingHoursStrings {

  protected $result = ['first_string' => '', 'second_string' => ''];

  public function getWorkingHoursStrings() {
    $this->result['first_string'] = 'Сейчас работаем';
    // Получаем все настройки по времени работы
    $config = \Drupal::config('working_hours_settings.admin_settings');

    $date = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));

    // проверка работаем ли сейчас или нет
    // получаем номер дня недели
    $day_of_weak = $date->format("N");
    // проверяем текущее время на время работы в обычные дни
    $in_interval = FALSE;
    switch ($day_of_weak) {
      case 1:
        $in_interval = $this->checkInterval($date, json_decode($config->get('mon_work_hours'), TRUE));
        break;
      case 2:
        $in_interval = $this->checkInterval($date, json_decode($config->get('tu_work_hours'), TRUE));
        break;
      case 3:
        $in_interval = $this->checkInterval($date, json_decode($config->get('we_work_hours'), TRUE));
        break;
      case 4:
        $in_interval = $this->checkInterval($date, json_decode($config->get('thu_work_hours'), TRUE));
        break;
      case 5:
        $in_interval = $this->checkInterval($date, json_decode($config->get('fr_work_hours'), TRUE));
        break;
      case 6:
        $in_interval = $this->checkInterval($date, json_decode($config->get('sat_work_hours'), TRUE));
        break;
      case 7:
        $in_interval = $this->checkInterval($date, json_decode($config->get('sun_work_hours'), TRUE));
        break;
    }

    // проверяем по исключениям
    $in_interval = $this->checkIntervalInExceptions($in_interval, $date, json_decode($config->get('exceptions_work_hours'), TRUE));

    if (!$in_interval) {
      $this->result['first_string'] = 'Сейчас закрыты';
    }

    return $this->result;
  }

  private function checkInterval($nowDate, $interval) {
    if ($interval) {
      $start_work_date_time = clone $nowDate;
      $start_parts = explode(':', $interval['start']);
      $start_work_date_time->setTime($start_parts[0], $start_parts[1]);

      $end_work_date_time = clone $nowDate;
      $end_parts = explode(':', $interval['end']);
      $end_work_date_time->setTime($end_parts[0], $end_parts[1]);
      if ($start_work_date_time < $nowDate && $nowDate < $end_work_date_time) {
        $before_close = $end_work_date_time->format("U") - $nowDate->format("U");
        if ($before_close < 3600) {
          $before_close_minutes = ceil($before_close / 60);
          $this->result['second_string'] = 'до закрытия ' . $before_close_minutes . ' минут';
        }
        else {
          $this->result['second_string'] = 'закроемся сегодня в ' . $interval['end'];
        }
        return TRUE;
      }
      if ($nowDate > $end_work_date_time) {
        $next_day = clone $nowDate;
        $next_day->add(new \DateInterval('P1D'));
        $next_start_time = $this->getNextStart($next_day);
        $this->result['second_string'] = 'откроемся завтра в ' . $next_start_time;
      }
      if ($nowDate < $start_work_date_time) {
        $before_open = $start_work_date_time->format("U") - $nowDate->format("U");
        if ($before_open >= 3600) {
          $this->result['second_string'] = 'откроемся сегодня в ' . $interval['start'];
        }
        else {
          $before_open_minutes = ceil($before_open / 60);
          $this->result['second_string'] = 'откроемся через ' . $before_open_minutes . ' минут';
        }
      }
    }
    return FALSE;
  }

  private function checkIntervalInExceptions($in_interval, $nowDate, $exceptions) {
    if ($exceptions) {
      foreach ($exceptions as $exception) {
        $dates = array_map("trim", explode('-', $exception['dates']));
        $exception_start_day = \DateTime::createFromFormat('d.m.Y', $dates[0]);
        $exception_start_day->setTime(0, 0);

        $exception_end_day = \DateTime::createFromFormat('d.m.Y', $dates[1]);
        $exception_end_day->setTime(23, 59);
        if ($exception_start_day > $nowDate || $nowDate > $exception_end_day) {
          continue;
        }
        unset($exception['dates']);
        $in_interval = $this->checkInterval($nowDate, $exception);
      }
    }
    return $in_interval;
  }

  private function getNextStart($day) {
    $config = \Drupal::config('working_hours_settings.admin_settings');
    $next_start = '';
    switch ($day_of_weak = $day->format("N")) {
      case 1:
        $next_start = json_decode($config->get('mon_work_hours'), TRUE)['start'];
        break;
      case 2:
        $next_start = json_decode($config->get('tu_work_hours'), TRUE)['start'];
        break;
      case 3:
        $next_start = json_decode($config->get('we_work_hours'), TRUE)['start'];
        break;
      case 4:
        $next_start = json_decode($config->get('thu_work_hours'), TRUE)['start'];
        break;
      case 5:
        $next_start = json_decode($config->get('fr_work_hours'), TRUE)['start'];
        break;
      case 6:
        $next_start = json_decode($config->get('sat_work_hours'), TRUE)['start'];
        break;
      case 7:
        $next_start = json_decode($config->get('sun_work_hours'), TRUE)['start'];
        break;
    }

    $next_start = $this->getNextStartExceptions($next_start, $day, json_decode($config->get('exceptions_work_hours'), TRUE));

    return $next_start;
  }

  private function getNextStartExceptions($next_start, $day, $exceptions) {
    if ($exceptions) {
      foreach ($exceptions as $exception) {
        $dates = array_map("trim", explode('-', $exception['dates']));
        $exception_start_day = \DateTime::createFromFormat('d.m.Y', $dates[0]);
        $exception_start_day->setTime(0, 0);

        $exception_end_day = \DateTime::createFromFormat('d.m.Y', $dates[1]);
        $exception_end_day->setTime(23, 59);
        if ($exception_start_day > $day || $day > $exception_end_day) {
          continue;
        }
        unset($exception['dates']);
        $next_start = $exception['start'];
      }
    }
    return $next_start;
  }
}
