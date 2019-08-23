<?php
namespace Drupal\ck_form_handler;

use Drupal\ck_form_handler\Event\СkFormHandlerEvent;
use \Drupal\node\Entity\Node;
use \Drupal\file\Entity\File;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class FormHandlerHelper {

  protected $response = 'Произошла ошибка, пожалуйста повторите попытку позже.';
  protected $to = 'dentkariesynet@gmail.com, kxz-stom@yandex.ru, shok20@kariesy.net, rr@keep-calm.ru, fm@keep-calm.ru, nebudetvlom@gmail.com';
  protected $subject = 'Запись на приём';
  protected $message = '';
  protected $headers = '';
  protected $valid = false;
  protected $formData = [];
  protected $source = '';
  protected $gaCid = '';

  public function __construct() {
    $this->source = isset($_COOKIE['ck_source']) && trim($_COOKIE['ck_source']) !== '' ? $_COOKIE['ck_source'] : 'Не удалось определить источник перехода';
    $this->gaCid = 'Не удалось определить client ID';
    if (isset($_COOKIE['_ga'])) {
      list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
      $contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
      $this->gaCid = $contents['cid'];
    }
  }

  public function sendEmail() {

      // Проверяем на валидность капчу от гугла
      if (!isset($this->formData['token'], $this->formData['action'])) {
        $this->response = 'Капча работает некорректно. Обратитесь к администратору!';

      }
      else {
        $captchaToken = $this->formData['token'];
        $captchaAction = $this->formData['action'];

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $params = [
          'secret' => '6LcDTI0UAAAAAKm6YzyjVHVeZXnBhzUmJa4TUYKg',
          'response' => $captchaToken,
          'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        if(!empty($response)){
          $decodedResponse = json_decode($response);
        }

        if ($decodedResponse && $decodedResponse->success && $decodedResponse->action == $captchaAction && $decodedResponse->score > 0) {
          // Определяем метод для обработки формы
          // на основании данных из поля "formName"
          $formHandlerMethod = 'defaultHandle';
          if (isset($this->formData['formName']) && class_exists(get_class($this), $this->formData['formName'] . 'Handle')) {
            $formHandlerMethod = $this->formData['formName'] . 'Handle';
          }
          $this->$formHandlerMethod();
          if ($this->valid) {
            $this->message .= "<br /><br /><br />Источник — {$this->source}<br />client ID: {$this->gaCid}";
            try {
              $mail = new PHPMailer(TRUE);
              $mail->CharSet = 'UTF-8';
              $mail->Encoding = 'base64';
              $mail->isHTML(TRUE);
              $this->message = nl2br($this->message);

              $mail->isSMTP();
              $mail->Host = 'smtp.yandex.ru';
              $mail->SMTPAuth = TRUE;
              $mail->Username = 'www-kariesy-net@yandex.ru';
              $mail->Password = '4SpnoC3WqQer';
              $mail->SMTPSecure = 'tls';
              $mail->Port = 587;

              $recipients = explode(',', $this->to);
              $recipients = array_map('trim', $recipients);
              $first_recipient = array_shift($recipients);
              $mail->setFrom('www-kariesy-net@yandex.ru', 'kariesy.net');
              $mail->addAddress($first_recipient);
              foreach ($recipients as $recipient) {
                $mail->addBCC($recipient);
              }

              $mail->Subject = $this->subject;
              $mail->Body = $this->message;
              $mail->AltBody = strip_tags($this->message);

              $mail->send();
            } catch (Exception $e) {
              if (strpos($this->headers, 'text/html') === false) {
                $this->message = preg_replace('#<br\s*/?>#i', "\n", $this->message);
              }
              mail($this->to, $this->subject, $this->message, $this->headers);
            }
          }
        }
      }
  }

  public function getResponse() {
    return $this->response;
  }

  /**
   * @param array $formData
   */
  public function setFormData(array $formData) {
    $this->formData = $formData;
  }

  protected function defaultHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($this->formData['what_exactly'], $this->formData['name'], $this->formData['phone']) ||
      empty($this->formData['what_exactly']) ||
      empty($this->formData['name']) ||
      empty($this->formData['phone'])
    ) {
      $this->response = 'Пожалуйста заполните все поля';
    }
    else {
      // Формируем тело письма
      $this->message = "Выбранная услуга: {$this->formData['what_exactly']}\n\n";
      $this->message .= "Данные заказчика\n\n";
      $this->message .= "Имя: {$this->formData['name']}\n";
      $this->message .= "Телефон: {$this->formData['phone']}\n";

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'Спасибо за обращение!';
      $this->response .= ' Наш менеджер свяжется с вами в ближайшее время.';
      $this->valid = true;
    }
  }

  protected function appointmentHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($this->formData['phone'], $this->formData['name']) ||
      empty($this->formData['name']) ||
      empty($this->formData['phone'])
    ) {
      $this->response = 'Пожалуйста укажите ваши имя и телефон';
    }
    else {
      // Подготавливаем дату желаемой записи для сохранения заявки
      $desired_date = explode('.', $this->formData['date']);
      $desired_date = implode('-', array_reverse($desired_date));
      $desired_date_time = $desired_date . 'T' . $this->formData['time'] . ':00';
      $node_create = [
        'type' => 'form_order',
        'title' => 'Запись на приём к врачу: ' . $this->formData['doctor'],
        'field_form_name' => 'Форма записи на приём',
        'field_phone' => $this->formData['phone'],
        'field_form_order_fio' => $this->formData['name'],
        'field_desired_date_and_time' => $desired_date_time,
        'field_misc_data' => json_encode([
          'UtmSource' => $_GET['utm_source'] ?? '',
          'UtmMedium' => $_GET['utm_medium'] ?? '',
          'UtmCampaign' => $_GET['utm_campaign'] ?? '',
          'UtmTerm' => $_GET['utm_term'] ?? '',
          'UtmContent' => $_GET['utm_content'] ?? '',
          'HttpReferer' => $this->source,
        ])
      ];

      $doctor_id = NULL;
      $slot_is_busy = FALSE;
      // Ищем идентификатор доктора по его имени
      if ($this->formData['doctor'] !== 'Любой') {
        $query = \Drupal::entityQuery('node')
          ->condition('status', 1)
          ->condition('type', 'doktor')
          ->condition('title', $this->formData['doctor'], 'LIKE');
        $nids = $query->execute();
        $doctor_id = reset($nids);

        // Делаем валидацию по времени записи, так как кто-то уже мог записаться
        // на это жде время и дату пока другой пользователь заполнял форму
        $desired_date_time = new \DateTime($desired_date_time);
        $doctor_node = Node::load($doctor_id);
        $ident_slots = $doctor_node->get('field_ident_slots')->value;
        if ($ident_slots) {
          $ident_slots = json_decode($ident_slots, TRUE);
          foreach ($ident_slots as $ident_slot) {
            $slot_date_time = explode('+', $ident_slot['StartDateTime']);
            $slot_date_time_start = new \DateTime($slot_date_time[0]);
            $slot_date_time_end = clone $slot_date_time_start;
            $slot_date_time_end->add(new \DateInterval('PT' . $ident_slot['LengthInMinutes'] . 'M'));
            if (
              $slot_date_time_start <= $desired_date_time
              && $slot_date_time_end > $desired_date_time
            ) {
              $slot_is_busy = $ident_slot['IsBusy'];
              break;
            }
          }
        }
        $busy_slots = $doctor_node->get('field_busy_slots_from_form')->value;
        if ($busy_slots) {
          $busy_slots = json_decode($busy_slots, TRUE);
          foreach ($busy_slots as $busy_slot) {
            $slot_date_time = explode('+', $busy_slot['StartDateTime']);
            $slot_date_time_start = new \DateTime($slot_date_time[0]);
            $slot_date_time_end = clone $slot_date_time_start;
            $slot_date_time_end->add(new \DateInterval('PT' . $busy_slot['LengthInMinutes'] . 'M'));
            if (
              $slot_date_time_start <= $desired_date_time
              && $slot_date_time_end > $desired_date_time
            ) {
              $slot_is_busy = $busy_slot['IsBusy'];
              break;
            }
          }
        }
      }

      if ($slot_is_busy) {
        $this->response = 'Выбранное вами время уже занял другой клиент, пожалуйста выберите другое время.';
      }
      else {

        // Формируем тело письма
        $this->message = "Запись на приём к врачу: {$this->formData['doctor']}\n\n";
        $this->message .= $node_create['field_comment'] = "Выбранная услуга: {$this->formData['service']}\n\n";
        $this->message .= "Желаемая дата приёма: {$this->formData['date']}\n\n";
        $this->message .= "Желаемое время приёма: {$this->formData['time']}\n\n";
        $this->message .= "Данные заказчика\n\n";
        $this->message .= "Имя: {$this->formData['name']}\n";
        $this->message .= "Телефон: {$this->formData['phone']}\n";
        if (isset($this->formData['comment']) && !empty($this->formData['comment'])) {
          $this->message .= "Комментарий\n {$this->formData['comment']}";
          $node_create['field_comment'] .= $this->formData['comment'];
        }

        $this->headers = 'From: robot@kariesy.net';
        $this->headers .= "\r\nReply-To: robot@kariesy.net";
        $this->headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
        $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

        $this->response = 'OK';
        $this->valid = TRUE;


        $node = Node::create($node_create);
        if ($doctor_id) {
          $node->field_form_order_doctor->target_id = $doctor_id;
          $this->formData += ['doctor_nid' => $doctor_id];
        }
        $node->save();

        /** @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $dispatcher */
        $dispatcher = \Drupal::service('event_dispatcher');

        // Create event object passing arguments.
        /** @var \Drupal\ck_form_handler\Event\СkFormHandlerEvent $event */
        $event = new СkFormHandlerEvent($this->formData);
        // Call it.
        $dispatcher->dispatch(СkFormHandlerEvent::APPOINTMENT_ORDER_SAVE, $event);
      }
    }
  }

  protected function recallHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($this->formData['phone'], $this->formData['name']) ||
      empty($this->formData['name']) ||
      empty($this->formData['phone'])
    ) {
      $this->response = 'Пожалуйста укажите ваши имя и телефон';
    }
    else {
      $this->subject = 'Заказ звонка';

      // Формируем тело письма
      $this->message = "\"{$this->formData['name']}\" просит с ним связаться по телефону \"{$this->formData['phone']}\"";

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'OK';
      $this->valid = true;
    }
  }

  protected function recallPriceHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($this->formData['phone'], $this->formData['name']) ||
      empty($this->formData['name']) ||
      empty($this->formData['phone'])
    ) {
      $this->response = 'Пожалуйста укажите ваши имя и телефон';
    } else {
      $this->subject = 'Запись на приём со страницы всех Цен';

      // Формируем тело письма
      $this->message = "\"{$this->formData['name']}\" хочет записаться на приём.<br />";
      $this->message .= "Телефон для связи \"{$this->formData['phone']}\"";

      if (isset($this->formData['whatPriceTab']) && !empty($this->formData['whatPriceTab'])) {
        $request = \Drupal::request();
        $referer = $request->headers->get('referer');
        list($tabText, $tabAnchor) = explode('#', $this->formData['whatPriceTab']);
        if (strpos($referer, 'price') === false) {
          $referer .= 'price';
        }
        $this->message .= "<br /><br />Ссылка на страницу с формой - <a href='{$referer}#{$tabAnchor}'>Цена на услугу \"$tabText\"</a>";
      }

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/html; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'OK';
      $this->valid = true;
    }
  }

  protected function questionHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($this->formData['phone'], $this->formData['name'], $this->formData['question']) ||
      empty($this->formData['name']) ||
      empty($this->formData['question']) ||
      empty($this->formData['phone'])
    ) {
      $this->response = 'Пожалуйста заполните все поля';
    }
    else {
      // Формируем тело письма
      $this->message = "Вопрос для доктора: {$this->formData['doctor']}\n\n";
      $this->message .= "Имя: {$this->formData['name']}\n";
      $this->message .= "Телефон: {$this->formData['phone']}\n\n";
      $this->message .= "Вопрос\n {$this->formData['question']}";

      $this->subject = 'Вопрос для доктора: ' . $this->formData['doctor'];

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'OK';
      $this->valid = true;
    }
  }

  protected function reviewHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($this->formData['fio'], $this->formData['review-text']) ||
      empty($this->formData['fio']) ||
      empty($this->formData['review-text'])
    ) {
      $this->response = 'Пожалуйста заполните все поля';
    }

    else {
      // Проверяем были ли переданы фотографии и обрабатываем их при наличии
      $files = [];
      $path = 'review/' . date('Y-m');
      foreach ($this->formData as $key => $value) {
        if (preg_match('/files(\d)+base/', $key, $matches)) {
          $img = \Drupal::service('ck_form_handler.base64_image_handler');
          $img->setBase64Image($value);
          $img->setFileName($this->formData['files'.$matches[1].'name']);
          $img->decodeBase64Image();
          $img->setFileDirectory($path);
          $file = file_save_data($img->getFileData(), 'public://' . $path . '/' . $img->getFileName() , FILE_EXISTS_REPLACE);
          $files[] = $file->id();
        }
      }

      // Создаём отзыв по переданным данным
      $nodeCreate = [
        'type'        => 'review',
        'title'       => $this->formData['fio'],
        'field_doctor' => [$this->formData['doctor']],
        'field_clinic' => [$this->formData['clinic']],
        'field_review_text' => $this->formData['review-text']
      ];
      if ($files) {
        foreach($files as $fid) {
          $nodeCreate['field_photos_of_review'][] = [
            'target_id' => $fid
          ];
        }
      }
      $node = Node::create($nodeCreate);
      $node->save();
      $nid = $node->id();
      $request = \Drupal::request();
      $origin = $request->headers->get('origin');

      // Формируем тело письма
      $bot_message = "{$this->formData['fio']} оставил(а) отзыв на сайте.\n\n";
      $this->message = "{$this->formData['fio']} оставил(а) отзыв на сайте {$origin}.\n\n";

      $bot_message .= "Текст отзыва:\n";
      $this->message .= "Текст отзыва:\n";

      $bot_message .= $this->formData['review-text'];
      $this->message .= $this->formData['review-text'];

      $this->message .= "\n\nСсылка для редактирования отзыва - {$origin}/node/{$nid}/edit";

      $this->subject = 'Новый отзыв на сайте "' . $origin . '"';

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'OK';
      $this->valid = true;

      // Отправляем данные отзыва в телеграм канал
      $bot_message .= "\n\n\nИсточник — {$this->source}\nclient ID: {$this->gaCid}";
      $telegram_bot = \Drupal::service('ck_form_handler.telegram_bot');
      $telegram_bot->wrapperSendOrderMessage($bot_message);
    }
  }

  protected function answerHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($this->formData['fio'], $this->formData['title'], $this->formData['question']) ||
      empty($this->formData['fio']) ||
      empty($this->formData['title']) ||
      empty($this->formData['question'])
    ) {
      $this->response = 'Пожалуйста заполните все поля';
    }
    else {

      // Создаём элемент сущности "Вопрос-ответ" по переданным данным
      $nodeCreate = [
        'type'        => 'vopros_otvet',
        'title'       => $this->formData['title'],
        'field_fio' => [$this->formData['fio']],
        'field_question' => [$this->formData['question']]
      ];
      $node = Node::create($nodeCreate);
      $node->setPublished(false);
      $node->save();
      $nid = $node->id();
      $request = \Drupal::request();
      $origin = $request->headers->get('origin');

      // Формируем тело письма
      $this->message = "\"{$this->formData['fio']}\" задал(а) вопрос с заголовком \"{$this->formData['title']}\"";
      $this->message .= "\n\nТекст вопроса:\n{$this->formData['question']}\n\n";
      $this->message .= "Ссылка для ответа на вопрос - {$origin}/node/{$nid}/edit";

      $this->subject = 'Новый вопрос на сайте "' . $origin . '"';

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'OK';
      $this->valid = true;
    }
  }

  protected function recallServiceHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($this->formData['phone'], $this->formData['name']) ||
      empty($this->formData['name']) ||
      empty($this->formData['phone'])
    ) {
      $this->response = 'Пожалуйста укажите ваши имя и телефон';
    } else {
      $this->subject = 'Запись на приём со страницы услуги';

      // Формируем тело письма
      $this->message = "\"{$this->formData['name']}\" хочет записаться на \"{$this->formData['whatExactlyService']}\"<br />";
      $this->message .= "Телефон для связи \"{$this->formData['phone']}\"";

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/html; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'OK';
      $this->valid = true;
    }
  }
}
