<?php

class formHandler {

  protected $response = 'Бот';

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
      // Проверяем что были переданы все праметры
      if (!isset($_POST['what_exactly'], $_POST['name'], $_POST['phone']) ||
        empty($_POST['what_exactly']) ||
        empty($_POST['name']) ||
        empty($_POST['phone'])
      ) {
        $this->response = 'Пожалуйста заполните все поля';
      }
      else {
        $to = 'rr@keep-calm.ru, fm@keep-calm.ru, nebudetvlom@mail.com';
        $subject = 'Запись на приём';

        // Формируем тело письма
        $message = "Выбранная услуга: {$_POST['what_exactly']}\n\n";
        $message .= "Данные заказчика\n\n";
        $message .= "Имя: {$_POST['name']}\n";
        $message .= "Телефон: {$_POST['phone']}\n";

        $headers = 'From: robot@kariesy.net';
        $headers .= "\r\nReply-To: robot@kariesy.net";
        $headers .= "\r\nContent-Type: text/plain; charset=\"utf-8\"";
        $headers .= "\r\nX-Mailer: PHP/" . PHP_VERSION;

        mail($to, $subject, $message, $headers);
        $this->response = 'Спасибо за обращение!';
        $this->response .= ' Наш менеджер свяжется с вами в ближайшее время.';
      }
    }
    $this->sendResponse();
  }

  protected function sendResponse() {
    echo $this->response;
  }

}

$formHandler = new formHandler();
$formHandler->sendEmail();
