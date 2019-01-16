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

}

$formHandler = new formHandler();
$formHandler->sendEmail();
