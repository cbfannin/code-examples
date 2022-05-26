<?php

namespace Drupal\my_points_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

/**
 * My Points Block.
 *
 * @Block(
 *   id = "my_points_block",
 *   admin_label = @Translation("My Points Block"),
 *   category = @Translation("Block")
 * )
 */
class MyPointsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->updatePoints();
    $points = $this->getPoints();
    $build = [];
    $block = [
      '#theme' => 'my_points_block',
      '#title' => $this->t('My Points'),
      '#points' => $points,
      '#cache' => [
        'max-age' => 0,
      ]
    ];
    $build['points_block'] = $block;
    return $build;
  }

  /**
   * Get Current User ID.
   * @return mixed
   */
  function getUserID() {
    $user = User::load(\Drupal::currentUser()->id());
    $uid = $user->get('uid')->value;
    return $uid;
  }

  /**
   * Update user's total points.
   * @return mixed
   */
  function updatePoints() {
    // Get all point_entry nodes for current user.
    $nids = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'point_entry')
      ->condition('field_points_assigned_to.target_id', $this->getUserID())
      ->execute();
    $nodes = Node::loadMultiple($nids);

    // Save point type ids associated with current user to array.
    foreach ($nodes as $node) {
      $earned_tids[] = $node->field_points_type->target_id;
    }

    // Get all point_type taxonomy term ids.
    $tids = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'point_type')
      ->execute();
    $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);

    // Loop through all point_type terms and compare point types
    // earned by user. If point types match, accumulate points.
    foreach ($terms as $term) {
      foreach ($earned_tids as $tid) {
        if ($term->tid->value == $tid) {
          $points += $term->field_point_value->value;
        }
      }
    }
    $this->savePoints($points);
  }

  /**
   * Save points to user profile after being updated.
   * @param $points
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  function savePoints($points) {
    $uid = $this->getUserID();
    $user = User::load($uid);
    $user->set('field_user_total_points', $points);
    $user->save();
  }

  /**
   * Return points from user profile.
   * @return mixed
   */
  function getPoints() {
    $user = User::load(\Drupal::currentUser()->id());
    $points = $user->get('field_user_total_points')->value;
    return $points;
  }
}
