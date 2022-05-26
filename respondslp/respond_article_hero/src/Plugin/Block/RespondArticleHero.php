<?php

namespace Drupal\respond_article_hero\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides an Article Hero Block.
 *
 * @Block(
 *   id = "respond_article_hero_block",
 *   admin_label = @Translation("Article Hero"),
 *   category = @Translation("Block"),
 * )
 */
class RespondArticleHero extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Create Node object and get title.
    $node = \Drupal::routeMatch()->getParameter('node');
    $title = $node->getTitle();

    // Create Media object and get url to image.
    $media = Media::load($node->field_article_image->target_id);
    $fid = $media->getSource()->getSourceFieldValue($media);
    $file = File::load($fid);
    $image = $file->url();

    // Create database query to get node's taxonomy terms.
    $query = \Drupal::database()
      ->select('taxonomy_index', 'ti')
      ->fields('ti', ['tid'])
      ->condition('nid', $node->id());
    $results = $query->execute()->fetchCol();
    $tids = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadMultiple($results);
    $tags = '<div>';
    foreach ($tids as $term) {
      $name = $term->getName();
      $url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]);
      $link = Link::fromTextAndUrl($name, $url);
      $link = $link->toRenderable();
      $tags .='<div class="article-hero-tag">'.render($link).'</div>';
    }
    $tags .= '</div>';

    return [
      '#theme' => 'respond_article_hero_theme',
      '#title' => $title,
      '#image' => $image,
      '#tags' => [
        '#markup' => $tags,
      ],
      '#attached' => [
        'library' => [
          'respond_article_hero/respond_article_hero',
        ],
      ],
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['user_list'],
        'max-age' => '0',
      ],
    ];
  }
}
