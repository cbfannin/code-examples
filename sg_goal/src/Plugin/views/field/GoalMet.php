<?php

/**
 * @file
 * Indicate Y if a goal has been met or N if not.
 */

namespace Drupal\sg_goal\Plugin\views\field;

use Drupal\Core\Database\Connection;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to display a sum of a goal's donations.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("goal_met")
 */
class GoalMet extends FieldPluginBase {

  protected ProgressTowardGoal $progress;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->progress = ProgressTowardGoal::create($container, $configuration, $plugin_id, $plugin_definition);
    return $instance;
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\sg_goal\Entity\Goal $goal */
    $goal = $values->_entity;
    if (!$goal) {
      return NULL;
    }

    $progress = $this->progress->render($values);

    if ($goal->get('bundle')->target_id == 'donation') {
      $target = $goal->get('field_donation_goal_amount')->value;
      $progress += $goal->get('field_offline_donations')->value;
    }
    elseif ($goal->get('bundle')->target_id == 'volunteers') {
      $target = $goal->get('field_volunteer_goal_amount')->value;
      $progress += $goal->get('field_offline_volunteer_requests')->value;
    }
    else {
      // Bundle is not one we anticipated.
      return NULL;
    }
    return $progress >= number_format($target, 2) ? 'Y' : 'N';
  }
}
