<?php
// Соотносим идентификаторы проектов с существующими сайтами.
// На одну точку входа должно быть не больше пяти сайтов.
$projects = [
  '931691' => 'http://kariesynet.tmweb.ru',
];

// TODO Отправляем запрос на принятие изменения соответствующему проекту
if (isset($projects[$_GET['projectid']])) {
  $url = $projects[$_GET['projectid']] . '/export_check?_format=json&';
  $url .= http_build_query($_GET);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = json_decode(curl_exec($ch));
  curl_close($ch);

  // Отдаём то что прислал плагин фиксации наличия страниц для экспорта
  if (is_array($output) && isset($output[0])) {
    echo $output[0];
  } else {
    echo $output;
  }
}
