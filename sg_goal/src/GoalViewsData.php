<?php

namespace Drupal\sg_goal;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the goal entity type.
 */
class GoalViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    return $data;
  }

}
