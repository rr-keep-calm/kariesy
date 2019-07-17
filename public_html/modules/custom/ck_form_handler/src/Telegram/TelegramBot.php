<?php

namespace Drupal\ck_form_handler\Telegram;

class TelegramBot {

  private $BOT_TOKEN = '*****';

  private $API_URL = '';

  private $chat_id = '*****';

  public function __construct() {
    $this->API_URL = 'https://api.telegram.org/bot' . $this->BOT_TOKEN . '/';
  }

  public function apiRequest($method, $parameters) {
    if (!is_string($method)) {
      error_log("Method name must be a string\n");
    }

    if (!$parameters) {
      $parameters = [];
    }
    else {
      if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
      }
    }

    foreach ($parameters as $key => &$val) {
      // encoding to JSON array parameters, for example reply_markup
      if (!is_numeric($val) && !is_string($val)) {
        $val = json_encode($val);
      }
    }
    $url = $this->API_URL . $method . '?' . http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return $this->exec_curl_request($handle);
  }

  public function exec_curl_request($handle) {
    $response = curl_exec($handle);

    if ($response === FALSE) {
      $errno = curl_errno($handle);
      $error = curl_error($handle);
      error_log("Curl returned error $errno: $error\n");
      curl_close($handle);
    }
    else {
      $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
      curl_close($handle);

      if ($http_code >= 500) {
        // do not wat to DDOS server if something goes wrong
        sleep(10);
      }
      else {
        if ($http_code != 200) {
          $response = json_decode($response, TRUE);
          error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
          if ($http_code == 401) {
            error_log('Invalid access token provided');
          }
        }
        else {
          $response = json_decode($response, TRUE);
          if (isset($response['description'])) {
            error_log("Request was successful: {$response['description']}\n");
          }
          $response = $response['result'];
        }
      }
    }

    return $response;
  }

  public function processMessage($message) {
    $chat_id = $message['chat']['id'];
    $this->apiRequest("sendMessage", [
      'chat_id' => $chat_id,
      "text" => 'Я не веду приватных бесед',
    ]);
  }

  public function wrapperSendOrderMessage($message) {
    if (!empty($message)) {
      $message = str_replace('&nbsp;', ' ', $message);

      if (strlen($message) > 4096) {
        // Делим строку по переносам
        $messageParts = explode(PHP_EOL, $message);
        $newMessage = '';
        foreach ($messageParts as $partOfMessage) {
          if (strlen($newMessage) + strlen($partOfMessage) > 4096) {
            // Отправляем часть сообщения о заказе
            $this->apiRequest("sendMessage", [
              'chat_id' => $this->chat_id,
              "text" => $newMessage,
              'parse_mode' => 'html',
            ]);
            $newMessage = $partOfMessage . PHP_EOL;
          }
          else {
            $newMessage .= $partOfMessage . PHP_EOL;
          }
        }
        $this->apiRequest("sendMessage", [
          'chat_id' => $this->chat_id,
          "text" => $newMessage,
          'parse_mode' => 'html',
        ]);
      }
      else {
        $this->apiRequest("sendMessage", [
          'chat_id' => $this->chat_id,
          "text" => $message,
          'parse_mode' => 'html',
        ]);
      }
    }
  }

}
