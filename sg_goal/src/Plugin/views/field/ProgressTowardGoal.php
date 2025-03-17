<?php

/**
 * @file
 * Provide a sum of donations or volunteers toward a given goal.
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
 * @ViewsField("progress_toward_goal")
 */
class ProgressTowardGoal extends FieldPluginBase {
  
  protected Connection $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->database = $container->get('database');
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

    $date_range = $goal->get('date_range');
    if ($goal->get('bundle')->target_id == 'donation') {
      // Get successful donations to the nonprofit during the date range.
      $query = $this->database->select('commerce_order_item', 'i');
      $query->join('commerce_order_item__field_donation_amount', 'd', 'd.entity_id = i.order_item_id');
      $query->join('commerce_order_item__field_nonprofit', 'n', 'n.entity_id = i.order_item_id');
      $query->join('commerce_order_item__field_payment_success', 's', 's.entity_id = i.order_item_id');
      $query->condition('i.changed', [$date_range->value, $date_range->end_value], 'BETWEEN');
      $query->condition('i.type', 'default');
      $query->condition('n.field_nonprofit_target_id', $goal->get('related_organizations')->target_id);
      $query->condition('s.field_payment_success_value', 1);
      $query->addExpression('SUM(field_donation_amount_number)', 'donation_sum');
      $sum = $query->execute()->fetchField() ?? 0;
      return number_format($sum, 2);
    }

    elseif ($goal->get('bundle')->target_id == 'volunteers') {
      // Get volunteer interest forms submitted to the nonprofit during the date range.
      $query = $this->database->select('webform_submission', 's');
      $query->join('webform_submission_data', 'n', 'n.sid = s.sid');
      $query->condition('n.name', 'nonprofit');
      $query->condition('n.value', $goal->get('related_organizations')->target_id);
      $query->condition('s.completed', [$date_range->value, $date_range->end_value], 'BETWEEN');
      $query->condition('s.webform_id', 'volunteer_application');
      $query->addExpression('COUNT(DISTINCT(s.sid))', 'submission_count');
      $count = $query->execute()->fetchField() ?? 0;
      return $count;
    }

    // Bundle is not one we anticipated.
    return NULL;
  }
}
