<?php

namespace Drupal\sitemap_additional_settings;


use Drupal\menu_link_content\Entity\MenuLinkContent;

class RefillSiteMapMenu {

  public function refillSiteMapMenu() {
    $map_levels = \Drupal::state()->get('map_levels', []);
    ksort($map_levels);
    $config = \Drupal::config('sitemap_additional.adminsettings');

    // Для начала удаляем ссылки которые удаляются наверняка - это добавленные
    // непосредственно в меню
    $mids = \Drupal::entityQuery('menu_link_content')
      ->condition('menu_name', $config->get('menu_for_auto_add'))
      ->execute();
    $controller = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $entities = $controller->loadMultiple($mids);
    $controller->delete($entities);

    // Если остались ссылки с большой долей вероятности это ссылки созданные из
    // views. Программно мы на них повлиять не можем, а значит потребуется
    // ручное вмешательство
    $parents = [];
    $host = \Drupal::request()->getSchemeAndHttpHost();
    foreach ($map_levels as $map_level) {
      foreach ($map_level as $level_item) {
        if ($level_item['path'] == '/') {
          continue;
        }
        $menu_link = [
          'title' => $level_item['name'],
          'link' => ['uri' => $host . $level_item['path']],
          'menu_name' => $config->get('menu_for_auto_add'),
          'expanded' => TRUE,
        ];
        if ($level_item['parent'] && isset($parents[$level_item['parent']])) {
          $menu_link['parent'] = $parents[$level_item['parent']];
        }
        $menu_link_content = MenuLinkContent::create($menu_link);
        $menu_link_content->save();
        $parents[$level_item['path']] = $menu_link_content->getPluginId();
      }
    }
    $custom_map_links = $config->get('custom_map_links');
    if ($custom_map_links) {
      foreach (explode("\n", $custom_map_links) as $custom_map_link) {
        $custom_map_link_parts = explode('|', $custom_map_link);
        if ($custom_map_link_parts[1] == '/') {
          continue;
        }
        $menu_link = [
          'title' => $custom_map_link_parts[0],
          'link' => ['uri' => $host . $custom_map_link_parts[1]],
          'menu_name' => $config->get('menu_for_auto_add'),
          'expanded' => TRUE,
        ];
        if (isset($custom_map_link_parts[2]) && isset($parents[trim($custom_map_link_parts[2])])) {
          $menu_link['parent'] = $parents[trim($custom_map_link_parts[2])];
        }
        $menu_link_content = MenuLinkContent::create($menu_link);
        $menu_link_content->save();
        $parents[trim($custom_map_link_parts[1])] = $menu_link_content->getPluginId();
      }
    }

    $cache = \Drupal::cache('menu');
    $cache->deleteAll();
    drupal_flush_all_caches();
  }
}
