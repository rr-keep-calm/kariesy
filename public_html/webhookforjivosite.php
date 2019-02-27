<?php
$inputData = file_get_contents('php://input');
$inputData = json_decode($inputData, TRUE);
if (
  $inputData &&
  isset($inputData['event_name']) &&
  (
    $inputData['event_name'] === 'chat_finished' ||
    $inputData['event_name'] === 'offline_message'
  )
) {
  $customData = [];
  $customDataTemp = explode('|||', $inputData['user_token']);
  foreach ($customDataTemp as $customDataItem) {
    list($key, $value) = explode('---', $customDataItem);
    $customData[$key] = $value;
  }

  $url = $customData['consultant_server_url'] . 'api/add_offline_message/';
  $text = $inputData['event_name'] === 'chat_finished' ? "Чат из JivoSite\n" : "Офлайн сообщение из JivoSite\n";

  // Формируем сообщение для coMagic
  if (isset($inputData['chat'])) {
    foreach ($inputData['chat']['messages'] as $chatMessage) {
      $text .= "{$chatMessage['type']}: {$chatMessage['message']}\n";
    }
  } elseif (isset($inputData['message'])) {
    $text .= $inputData['message'];
  }

  $data = [
    'site_key' => $customData['site_key'],
    'visitor_id' => $customData['visitor_id'],
    'hit_id' => $customData['hit_id'],
    'session_id' => $customData['session_id'],
    'name' => $inputData['visitor']['name'],
    'email' => $inputData['visitor']['email'],
    'phone' => $inputData['visitor']['phone'],
    'text' => $text,
  ];

  $options = [
    'http' =>
      [
        'header' => "Content-type: application/x-www-form-urlencoded; charset=UTF-8",
        'method' => "POST",
        'content' => http_build_query($data),
      ],
  ];
  $context = stream_context_create($options);
  $result = file_get_contents($url, FALSE, $context);
  $resultArray = json_decode($result, TRUE);

  // логируем запросы
  $date = date('Y-m-d H:i:s');
  $coMagicResponseSuccess = $resultArray['success'] ? 'true' : 'false';
  $logText = "\n\n{$date} - {$inputData['email']} - {$coMagicResponseSuccess}";
  file_put_contents(dirname(__FILE__) . '/logJivo', $logText, FILE_APPEND);
}
