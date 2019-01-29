<?php
namespace Drupal\ck_form_handler;

class FormHandlerHelper {

  protected $response = 'Бот';
  protected $to = 'rr@keep-calm.ru, fm@keep-calm.ru, nebudetvlom@gmail.com';
  protected $subject = 'Запись на приём';
  protected $message = '';
  protected $headers = '';
  protected $valid = false;
  protected $formData = [];

  public function __construct($formData) {
    $this->formData = $formData;
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
            mail($this->to, $this->subject, $this->message, $this->headers);
          }
        }
      }
  }

  public function getResponse() {
    return $this->response;
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
      // Формируем тело письма
      $this->message = "Запись на приём ко врачу: {$this->formData['doctor']}\n\n";
      $this->message .= "Выбранная услуга: {$this->formData['service']}\n\n";
      $this->message .= "Желаемая дата приёма: {$this->formData['date']}\n\n";
      $this->message .= "Желаемое время приёма: {$this->formData['time']}\n\n";
      $this->message .= "Данные заказчика\n\n";
      $this->message .= "Имя: {$this->formData['name']}\n";
      $this->message .= "Телефон: {$this->formData['phone']}\n";
      if (isset($this->formData['comment']) && !empty($this->formData['comment'])) {
        $this->message .= "Комментарий\n {$this->formData['comment']}";
      }

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'OK';
      $this->valid = true;
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
}