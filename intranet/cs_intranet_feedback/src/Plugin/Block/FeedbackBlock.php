<?php

namespace Drupal\cs_intranet_feedback\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a 'FeedbackBlock' block.
 *
 * @Block(
 *  id = "feedback_block",
 *  admin_label = @Translation("Feedback Block"),
 * )
 */
class FeedbackBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $url = $_SERVER['REQUEST_URI'];
    $node = \Drupal::routeMatch()->getParameter('node');
    $nid = $node->id();

    $build = [];
    $build['feedback_block'] = [
      '#markup' => '<a class="btn btn-default btn-xs pull-right" href="/feedback-form?page=' . $url . '&nid=' . $nid . '">Report an Issue</a>',
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $build;
  }

}
