<?php
// @codingStandardsIgnoreFile
return array(
    'pageroot' => "", // Корневая папка сайта на диске (только если скрипт без этого не работает)
    'website' => "https://kariesy.net/", // Сайт для сканирования
    'sitemap_file' => "/sitemap.xml", // Файл для записи xml-карты сайта
    'script_timeout' => "60", // Максимальное время выполнения скрипта (секунды)
    'load_timeout' => "10", // Максимальное время ожидания получения одного URL (секунды)
    'recording' => "0.5", // Минимальное время на запись в промежуточный файл (секунды)
    'existence_time_file' => "25", // Максимальное время существования версии промежуточного файла (часы)
    'delay' => "1", // Задержка между запросами URL (секунды)
    'tmp_file' => "/../tmp/sitemap.part", // Путь от корня сайта к временному файлу
    'old_sitemap' => "/../tmp/sitemap-old.part", // Путь от корня сайта к файлу предыдущего сканирования
    'is_radar' => "0", // Собирать перелинковку
    'tmp_radar_file' => "/../tmp/radar.part", // Путь от корня сайта к временному файлу отчёта о перелинковке
    'old_radar_file' => "/../tmp/radar-old.part", // Путь от корня сайта к файлу предыдущего отчёта о перелинковке
    'change_freq' => "weekly", // Частота обновления страниц
    'priority' => "0.8", // Приоритет для всех страниц
    'time_format' => "long", // Формат отображения времени
    'disallow_regexp' => "/\.(xml|inc|txt|js|zip|bmp|jpg|jpeg|png|gif|css)$/i", // Регулярные выражения для файлов, которые не надо включать в карту сайта
    'disallow_key' => "sid\nPHPSESSID", // GET параметры, отбрасываемые при составлении карты сайта
    'seo_urls' => "http://example.com/promoted-page.html = 0.9", // Приоритет для продвигаемых ссылок
    'email_cron' => "", // Электронная почта для cron-сообщений
    'email_notify' => "nebudetvlom@gmail.com", // Электронная почта для уведомления о добавленных/удалённых ссылках
    'email_json' => "", // Электронная почта для уведомлений об изменениях в карте сайта (json-формат)
);
