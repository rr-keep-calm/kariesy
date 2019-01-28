<?php

class formHandler {

  protected $response = 'Бот';
  protected $to = 'rr@keep-calm.ru, fm@keep-calm.ru, nebudetvlom@gmail.com';
  protected $subject = 'Запись на приём';
  protected $message = '';
  protected $headers = '';
  protected $valid = false;

  /**
   * Проверка на передачу данных формы методом POST
   *
   * @return bool
   */
  protected function isPostRequest() {
    return (isset($_SERVER['REQUEST_METHOD']) && (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST'));
  }

  public function sendEmail() {
    if ($this->isPostRequest()) {

      // Проверяем на валидность капчу от гугла
      if (!isset($_POST['token'], $_POST['action'])) {
        $this->response = 'Капча работает некорректно. Обратитесь к администратору!';

      }
      else {
        $captcha_token = $_POST['token'];
        $captcha_action = $_POST['action'];

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $params = [
          'secret' => '6Lfwl4MUAAAAAKCP8ZV13J6ngN_A9RiPtzxM9CDi',
          'response' => $captcha_token,
          'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        if(!empty($response)) $decoded_response = json_decode($response);

        $success = false;

        if ($decoded_response && $decoded_response->success && $decoded_response->action == $captcha_action && $decoded_response->score > 0) {
          $success = $decoded_response->success;
          // обрабатываем данные формы, которая защищена капчей
        } else {
          // прописываем действие, если пользователь оказался ботом
        }

        echo json_encode($result);


        // Определяем метод для обработки формы
        // на основании данных из поля "formName"
        $formHandlerMethod = 'defaultHandle';
        if (isset($_POST['formName']) && class_exists(get_class($this), $_POST['formName'] . 'Handle')) {
          $formHandlerMethod = $_POST['formName'] . 'Handle';
        }
        $this->$formHandlerMethod();
        if ($this->valid) {
          mail($this->to, $this->subject, $this->message, $this->headers);
        }
      }
    }
    $this->sendResponse();
  }

  protected function sendResponse() {
    echo $this->response;
  }

  protected function defaultHandle()
  {
    // Проверяем что были переданы все праметры
    if (!isset($_POST['what_exactly'], $_POST['name'], $_POST['phone']) ||
      empty($_POST['what_exactly']) ||
      empty($_POST['name']) ||
      empty($_POST['phone'])
    ) {
      $this->response = 'Пожалуйста заполните все поля';
    }
    else {
      // Формируем тело письма
      $this->message = "Выбранная услуга: {$_POST['what_exactly']}\n\n";
      $this->message .= "Данные заказчика\n\n";
      $this->message .= "Имя: {$_POST['name']}\n";
      $this->message .= "Телефон: {$_POST['phone']}\n";

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
    if (!isset($_POST['phone'], $_POST['name']) ||
      empty($_POST['name']) ||
      empty($_POST['phone'])
    ) {
      $this->response = 'Пожалуйста укажите ваши имя и телефон';
    }
    else {
      // Формируем тело письма
      $this->message = "Запись на приём ко врачу: {$_POST['doctor']}\n\n";
      $this->message .= "Выбранная услуга: {$_POST['service']}\n\n";
      $this->message .= "Желаемая дата приёма: {$_POST['date']}\n\n";
      $this->message .= "Желаемое время приёма: {$_POST['time']}\n\n";
      $this->message .= "Данные заказчика\n\n";
      $this->message .= "Имя: {$_POST['name']}\n";
      $this->message .= "Телефон: {$_POST['phone']}\n";
      if (isset($_POST['comment']) && !empty($_POST['comment'])) {
        $this->message .= "Комментарий\n {$_POST['comment']}";
      }

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
    if (!isset($_POST['phone'], $_POST['name'], $_POST['question']) ||
      empty($_POST['name']) ||
      empty($_POST['question']) ||
      empty($_POST['phone'])
    ) {
      $this->response = 'Пожалуйста заполните все поля';
    }
    else {
      // Формируем тело письма
      $this->message = "Вопрос для доктора: {$_POST['doctor']}\n\n";
      $this->message .= "Имя: {$_POST['name']}\n";
      $this->message .= "Телефон: {$_POST['phone']}\n\n";
      $this->message .= "Вопрос\n {$_POST['question']}";

      $this->subject = 'Вопрос для доктора: ' . $_POST['doctor'];

      $this->headers = 'From: robot@kariesy.net';
      $this->headers .= "\r\nReply-To: robot@kariesy.net";
      $this->headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
      $this->headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

      $this->response = 'OK';
      $this->valid = true;
    }
  }

}

$formHandler = new formHandler();
$formHandler->sendEmail();
