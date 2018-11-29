<?php

namespace Drupal\tilda_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

class TildaController extends ControllerBase {

  protected $apiUrl = 'http://api.tildacdn.info/v1/';

  /**
   * {@inheritdoc}
   */
  public function tilda() {
    // Получаем одну страницу для экспорта из Тильды
    $row = \Drupal::database()
      ->select('tilda_need_export', 't')
      ->fields('t')
      ->orderBy('ID', 'ASC')
      ->range(0, 1)
      ->execute()
      ->fetch();

    // TODO Запускаем процедуру экспорта если есть что экспортировать

    // Получаем информацию по проекту в целом getprojectexport.
    // Здесь нам нужно получить все js,css и картинки для проекта в целом.
    // Получаем сохраняем.

    // Формируем запрос к Тильде
    // Добавляем путь для получения информации по проекту
    $url = $this->apiUrl . 'getprojectexport/';

    // Добавляем публичный ключ
    $url .= '?publickey=2qp1xbf14gkcf3q7ljbd';

    // Добавляем секретный ключ
    $url .= '&secretkey=zthyh3j6p60rwpst17v5';

    // Добавляем идентификатор проекта
    $url .= '&projectid=' . $row->projectid;

    $result = file_get_contents($url);
    $project = json_decode($result, TRUE);

    $path = DRUPAL_ROOT . DIRECTORY_SEPARATOR;
    $path .= drupal_get_path('module', 'tilda_export') . DIRECTORY_SEPARATOR;

    // Проходим по всем css файлам из Тильды и сохраняем их для вписания в
    // библиотеки модуля
    $cssLibraryString = '';
    foreach ($project['result']['css'] as $cssItem) {
      $cssContent = file_get_contents($cssItem['from']);
      if ($cssContent) {
        file_put_contents("{$path}css/{$cssItem['to']}", $cssContent);
        $cssLibraryString .= "      css/{$cssItem['to']}: {}\n";
      }
    }

    // Проходим по всем js файлам из Тильды и сохраняем их для вписания в
    // библиотеки модуля
    $jsLibraryString = '';
    foreach ($project['result']['js'] as $jsItem) {
      // Исключаем добавление некоторых скриптов из Тильды,
      // чтобы не конфликтовали с уже имеющимися. Например jQuery разных версий.
      // TODO перенести список скриптов в настройки
      if ((string)$jsItem['to'] === 'jquery-1.10.2.min.js') {
        continue;
      }
      $jsContent = file_get_contents($jsItem['from']);
      if ($jsContent) {
        file_put_contents("{$path}js/{$jsItem['to']}", $jsContent);
        $jsLibraryString .= "    js/{$jsItem['to']}: {}\n";
      }
    }

    // Дописываем css и js для подключения в библиотеках
    $moduleLibraryContent = <<<LIBRARYCONTENT
tilda:
  version: 1
  js:
|||jsFromTilda|||
  css:
    theme:
|||cssFromTilda|||
LIBRARYCONTENT;

    $moduleLibraryContent = str_replace('|||jsFromTilda|||',
      $jsLibraryString,
      $moduleLibraryContent
    );

    $moduleLibraryContent = str_replace('|||cssFromTilda|||',
      $cssLibraryString,
      $moduleLibraryContent
    );

    $path .= 'tilda_export.libraries.yml';
    file_put_contents($path, $moduleLibraryContent);

    // Сохраняем общие для сайта картинки
    $path = DRUPAL_ROOT . DIRECTORY_SEPARATOR;
    $path .= drupal_get_path('theme', 'fromTilda') . DIRECTORY_SEPARATOR;
    foreach ($project['result']['images'] as $img) {
      copy($img['from'], "{$path}img/{$img['to']}");
    }

    // Данные из htaccess не трогаем (по крайней мере пока)

    // Получаем определённую страницу от Тильды для экспорта
    // Формируем запрос к Тильде
    // Добавляем путь для получения информации по проекту
    $url = $this->apiUrl . 'getpageexport/';

    // Добавляем публичный ключ
    $url .= '?publickey=2qp1xbf14gkcf3q7ljbd';

    // Добавляем секретный ключ
    $url .= '&secretkey=zthyh3j6p60rwpst17v5';

    // Добавляем идентификатор проекта
    $url .= '&pageid=' . $row->pageid;

    $result = file_get_contents($url);
    $page = json_decode($result, TRUE);

    // Пытаемся получить идентификатор ноды по адресу
    $id = 0;
    $alias = '/' . $page['result']['alias'];
    $url = Url::fromUri('internal:' . $alias);
    if ($url->isRouted()) {
      $params = $url->getRouteParameters();
      $entity_type = key($params);
      $etm = \Drupal::entityTypeManager();
      $entity = $etm->getStorage($entity_type)
        ->load($params[$entity_type]);
      $id = $entity->id();
    }

    // Если ноды нет, то создаём её
    if (!$id) {
      $node = Node::create([
        'type' => 'page',
        'title' => $page['result']['title'],
      ]);
      $node->save();
      $id = $node->id();

      // Формируем пояснительный текст для удобного просмотра в админке
      $body = 'Материал был экспортирован из сервиса "Тильда"<br/>';
      $body .= 'Весь текст материала расположен в шаблоне для обеспечения';
      $body .= ' отображения "как есть"<br/>';
      $body .= 'Путь до шаблона - ';
      $body .= "\"{$path}templates/tildaExport/page--node--{$id}.html.twig\"";
      $node->set('body', [
        'value' => $body,
        'summary' => '',
        'format' => 'full_html',
      ]);
      $node->save();
      // Подключаем к ноде синоним (адрес)
      \Drupal::service('path.alias_storage')
        ->save('/node/' . $id, $alias, 'ru');
    } else {
      // TODO обновить тело ноды чтобы вписать пояснительный текст
    }

    // Получаем HTML код новой страницы
    $html = $page['result']['html'];

    // Сохраняем картинки которые используются на странице
    foreach ($page['result']['images'] as $img) {
      copy($img['from'], "{$path}img/{$img['to']}");

      // Ссылки на картинки подменяем правильными
      $html = str_replace($img['to'],
        '{{ directory }}/img/' . $img['to'],
        $html
      );
    }

    // Записываем html-код страницы в файл шаблона темы
    $htmlTwig = <<<HTMLTWIG
{% extends  "page.html.twig" %}

{% block content %}
    {$html}
{% endblock %}
HTMLTWIG;
    $templateFilePath = $path . 'templates/tildaExport/page--node--' . $id;
    $templateFilePath .= '.html.twig';
    file_put_contents($templateFilePath, $htmlTwig);

    // TODO Удаляем из базы запись о надобности экспорта страницы после удачного
    // импорта на сайт

    // TODO Чистим весь кэш
    drupal_flush_all_caches();

    // TODO  Шлак для вывода страницы удалить, так как перенесётся всё в хук
    $output = [];
    $output['#title'] = 'HelloWorld page title';
    $output['#markup'] = 'Hello World!';
    return $output;
  }

}
