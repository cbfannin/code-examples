<?php

namespace Drupal\point_entry_create_node\Plugin\WebformHandler;

use Drupal\node\Entity\Node;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\user\Entity\User;

/**
 * Create a new node entity from a webform submission.
 *
 * @WebformHandler(
 *   id = "Point Entry Create Node",
 *   label = @Translation("Point Entry Create Node"),
 *   category = @Translation("Entity Creation"),
 *   description = @Translation("Creates a new node from Point Entry Webform Submission."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class PointEntryCreateNodeHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */

  // Function to be fired after submitting the Webform.
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    // Get an array of the values from the submission.
    $values = $webform_submission->getData();

    // Get current user.
    $user = User::load(\Drupal::currentUser()->id());
    $uid= $user->get('uid')->value;
    $roles = $user->getRoles();

    // Determine what entity reference field to get points type from.
    if (in_array('employee', $roles)) {
      $points_type = $values['point_type_employee_'];
    } elseif (in_array('student', $roles)) {
      $points_type = $values['point_type_student_'];
    }

    $node_args = [
      'type' => 'point_entry',
      'langcode' => 'en',
      'created' => time(),
      'changed' => time(),
      'uid' => 1,
      'moderation_state' => 'published',
      'field_points_assigned_to' => $uid,
      'field_points_type' => $points_type
    ];

    $node = Node::create($node_args);
    $node->save();

  }
}
