<?php
namespace Drupal\rss_articles_feed;

use Drupal\views\Views;

class FeedFileCreator {

  public function create() {
    $result = [];

    // Получаем активные первые уровни словаря "Услуга"
    $articles = \Drupal::entityTypeManager()
      ->getStorage('node')->loadByProperties(['type' => 'article', 'status' => '1']);

    // проходим по всем услугам
    $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
    foreach ($articles as $article) {
      $article_build = $view_builder->view($article, 'full');

      $relevant_services_on_article_page_for_feed = '';
      $view = Views::getView('recommended_services');
      $view->setDisplay('block_2');
      $relevant_services = $article->get('field_relevant_services')->getValue();
      array_walk($relevant_services, function (&$item) {
        $item = $item['target_id'];
      });
      if ($relevant_services) {
        $view->setArguments([implode(',', $relevant_services)]);
      }
      $render = $view->render();
      if ($render['#rows'] && count($render['#rows']) > 0) {
        $relevant_services_on_article_page_for_feed = \Drupal::service('renderer')->renderRoot($render);
      }

      $build = [
        'page' => [
          '#theme' => 'article_page_for_feed',
          '#data' => [
            'title' => $article->getTitle(),
            'article_content' => \Drupal::service('renderer')->renderRoot($article_build),
            'article_image' => file_create_url($article->field_article_image->entity->getFileUri()),
            'relevant_services_on_article_page_for_feed' => str_replace('header', 'div', $relevant_services_on_article_page_for_feed)
          ],
        ],
      ];

      $article_page_for_feed = (string) \Drupal::service('renderer')->renderRoot($build);
      $article_variables = [
        'url' => $article->toUrl('canonical', ['absolute' => TRUE])->toString(),
        'body' => $article_page_for_feed
      ];

      $result['articles'][$article->id()] = $article_variables;
    }

    return $result;
  }
}
