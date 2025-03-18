<?php

/**
 * Implements hook_form_FORM_ID_alter().
 */
function sg_goal_form_node_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if ($form_id == 'node_nonprofit_edit_form' || $form_id == 'node_group_hub_edit_form') {
    $nid = $form_state->getformObject()->getEntity()->id();
    // Add donation goals button.
    $form['group_goals']['button_donations'] = array(
      '#markup' => t(' <div id="donations-btn" class="button button--primary btn btn-secondary border-2 px-3 ">
      <a href="@donations">Donation goals</a></div>',
        array('@donations' => "/node/$nid/donation-goals"))
    );
    // Add volunteer goals button.
    $form['group_goals']['button_volunteers'] = array(
      '#markup' => t(' <div id="volunteers-btn" class="button button--primary btn btn-secondary border-2 px-3 ">
      <a href="@volunteers">Volunteer goals</a></div>',
        array('@volunteers' => "/node/$nid/volunteer-goals"))
    );
  }
}

/**
 * Implements hook_form_alter().
 */
function sg_goal_form_goal_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  $entity = $form_state->getformObject()->getEntity();

  $query = \Drupal::request()->query->all();
  $related_organization_id = (!empty($query['edit']) && $query['edit']['related_organizations']['widget'][0]['target_id']) || !empty($entity->get('related_organizations')->target_id) ?? NULL;
  $form['related_organizations']['#disabled'] = TRUE;

  if (!$related_organization_id) {
    $form['#prefix'] = '<h3 class="text-center">You must create goals within an organization profile.</h3>
    <h3 class="text-center">Edit the organization profile and click its Goals tab.</h3>';
    // Disable the form fields.
    $form['#disabled'] = TRUE;
  }

  if (
    $form_id == 'goal_donation_add_form'
    || $form_id == 'goal_donation_edit_form'
    || $form_id == 'goal_donation_delete_form'
  ) {
    $form['actions']['submit']['#submit'][] = 'sg_goal_donation_redirect_submit';
  }

  if (
    $form_id == 'goal_volunteers_add_form'
    || $form_id == 'goal_volunteers_edit_form'
    || $form_id == 'goal_volunteers_delete_form'
  ) {
    $form['actions']['submit']['#submit'][] = 'sg_goal_volunteers_redirect_submit';
  }
}



/**
 * Custom submission handler for the goal donation entity forms.
 */
function sg_goal_donation_redirect_submit(array &$form, FormStateInterface $form_state) {
  $related_orgs = $form_state->getValue('related_organizations');
  if (!empty($related_orgs) && isset($related_orgs[0]['target_id'])) {
    $node_id = $related_orgs[0]['target_id'];
    $path = 'node/' . $node_id . '/donation-goals';
    $form_state->setRedirectUrl(Url::fromUserInput('/' . $path));
  }
}

/**
 * Custom submission handler for the goal volunteers entity forms.
 */
function sg_goal_volunteers_redirect_submit(array &$form, FormStateInterface $form_state) {
  $related_orgs = $form_state->getValue('related_organizations');
  if (!empty($related_orgs) && isset($related_orgs[0]['target_id'])) {
    $node_id = $related_orgs[0]['target_id'];
    $path = 'node/' . $node_id . '/volunteer-goals';
    $form_state->setRedirectUrl(Url::fromUserInput('/' . $path));
  }
}

/**
 * Implements hook_preprocess_HOOK() for views_view_unformatted templates.
 */
function sg_goal_preprocess_goal(&$variables) {
  $goal_entity = $variables['elements']['#goal'];
  $node_type = $goal_entity->get('related_organizations')->entity->bundle();

  if ($goal_entity->bundle() == 'donation') {

    // Query to get the total donations between goal dates.
    $query = Database::getConnection()->select(
      'commerce_order_item',
      NULL
    );
    $query->fields('commerce_order_item', ['order_item_id']);
    $query->addExpression(
      'SUM(commerce_order_item.total_price__number)',
      'goal_progress'
    );
    $query->addExpression(
      'COUNT(DISTINCT commerce_order.order_id)',
      'goal_supporters'
    );

    // If node type is nonprofit join these tables.
    if ($node_type == 'nonprofit') {
      $query->leftJoin(
        'commerce_order_item__field_nonprofit',
        NULL,
        'commerce_order_item.order_item_id = commerce_order_item__field_nonprofit.entity_id
      AND commerce_order_item__field_nonprofit.deleted = 0'
      );
      $query->innerJoin(
        'node_field_data',
        NULL,
        'commerce_order_item__field_nonprofit.field_nonprofit_target_id = node_field_data.nid'
      );
    }

    // If node type is group_hub join these tables.
    if ($node_type == 'group_hub') {
      $query->leftJoin(
        'commerce_order_item__field_associated_org',
        NULL,
        'commerce_order_item.order_item_id = commerce_order_item__field_associated_org.entity_id
      AND commerce_order_item__field_associated_org.deleted = 0'
      );
      $query->innerJoin(
        'node_field_data',
        NULL,
        'commerce_order_item__field_associated_org.field_associated_org_target_id = node_field_data.nid'
      );
    }

    $query->leftJoin(
      'commerce_order',
      NULL,
      'commerce_order_item.order_id = commerce_order.order_id'
    );
    $query->innerJoin(
      'goal',
      NULL,
      'node_field_data.nid = goal.related_organizations'
    );

    $query->condition('goal.id', $goal_entity->id());
    $query->condition(
      'commerce_order_item.changed', [$goal_entity->get('date_range')->value, $goal_entity->get('date_range')->end_value],
      'BETWEEN'
    );

    $results = $query->execute()->fetchAll();

    $variables['goal_progress'] = $goal_entity->get('field_offline_donations')->value + $results[0]->goal_progress;
    $variables['goal_supporters'] = $results[0]->goal_supporters;
  }

  if ($goal_entity->bundle() == 'volunteers') {

    // Query to get the total volunteer applications between goal dates.
    $query = \Drupal::database()->select(
      'webform_submission',
      NULL
    );
    $query->addExpression(
      'COUNT(webform_submission.sid)',
      'goal_volunteers_applications'
    );
    $query->addExpression(
      'COUNT(DISTINCT email_address_data.value)',
      'goal_volunteers'
    );
    $query->leftJoin(
      'webform_submission_data',
      NULL,
      "webform_submission.sid = webform_submission_data.sid
      AND webform_submission_data.name =  :node_type
      AND webform_submission_data.delta = '0'
      AND webform_submission_data.property = ''",
      [':node_type' => $node_type]);
    $query->innerJoin(
      'node_field_data',
      NULL,
      'webform_submission_data.value = node_field_data.nid'
    );
    $query->leftJoin(
      'webform_submission_data',
      'email_address_data',
      "webform_submission.sid = email_address_data.sid
      AND email_address_data.name = 'email_address'
      AND email_address_data.delta = '0'
      AND email_address_data.property = ''"
    );

    $related_organization = $goal_entity->get('related_organizations')->entity;
    $node_id = $related_organization->id();

    $query->condition('node_field_data.nid', $node_id);
    $query->condition('webform_submission.webform_id', 'volunteer_application');
    $query->condition('webform_submission.completed', [
      $goal_entity->get('date_range')->value,
      $goal_entity->get('date_range')->end_value
    ], 'BETWEEN');

    $results = $query->execute()->fetchAll();

    $variables['goal_volunteers_applications'] = $goal_entity->get('field_offline_volunteer_requests')->value + $results[0]->goal_volunteers_applications;
    $variables['goal_volunteers'] = $results[0]->goal_volunteers;
  }
}
