<?php

namespace Drupal\cs_intranet_feedback\Form;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\Plugin\Mail\PhpMail;

/**
 * Class FeedbackForm.
 */
class FeedbackForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'feedback_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to Issue'),
      '#description' => $this->t('This is the path where the issue exists.
        If this path is incorrect, please visit the page where you discovered
        the issue and report it from there.'),
      '#value' => $_SERVER['SERVER_NAME'] . $_GET['page'],
      '#disabled' => TRUE,
    ];
    $form['feedback_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Feedback Type'),
      '#description' => $this->t('Select the type of feedback being submitted.'),
      '#options' => [
        '0' => $this->t('Technical'),
        '1' => $this->t('Outdated/Wrong Information'),
        '2' => $this->t('Grammar/Spelling/Punctuation'),
      ],
      '#required' => TRUE,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('Provide a detailed description of the problem.'),
      '#required' => TRUE,
    ];
    $form['nid'] = [
      '#type' => 'hidden',
      '#title' => $this->t('nid'),
      '#value' => $_GET['nid'],
      '#disabled' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->messenger()->addMessage($this->t('Form submitted successfully.'));

    $sendMail = new PhpMail();
    $siteName = \Drupal::config('system.site')->get('name');
    $user = \Drupal::currentUser();
    $userEmail = $user->getEmail();
    $userAccountName = $user->getAccountName();
    try {
      $user = \Drupal::entityTypeManager()
        ->getStorage('user')
        ->load($user->id());
    }
    catch (InvalidPluginDefinitionException $e) {
    }
    catch (PluginNotFoundException $e) {
    }
    $userName = $user->field_first_name->value . " " . $user->field_last_name->value;
    // Determine name to use.
    if (!isset($userName)) {
      $name = $userName;
    }
    else {
      $name = $userAccountName;
    }
    $from = \Drupal::config('system.site')->get('mail');
    $node = entity_load('node', $form_state->getValue('nid'));
    $to = $node->uid->entity->mail->value;
    $message['headers'] = [
      'content-type' => 'text/html',
      'MIME-Version' => '1.0',
      'reply-to' => $userEmail,
      'from' => $siteName . ' <' . $from . '>',
    ];
    $message['to'] = $to;
    $feedbackKey = $form_state->getValue('feedback_type');
    $feedbackVal = $form['feedback_type']['#options'][$feedbackKey];
    $message['subject'] = 'Intranet Issue: ' . $feedbackVal;
    $message['body'] = "<p>Dear " . $name . ",<br/><br/>"
      . "An issue has been discovered on a page you help manage.<br/><br/>"
      . "<strong>Here are the details:</strong><br/>"
      . $form_state->getValue('description')
      . "<br/><br/> The issue was found here: "
      . "<a href='" . $form_state->getValue('url') . "'>"
      . $form_state->getValue('url') . "</a><br/>"
      . "If you would like to correct this issue, you may log in here: "
      . "<a href='" . $_SERVER['SERVER_NAME'] . "/user'>"
      . $_SERVER['SERVER_NAME'] . "/user</a></p>";

    // E-mail result.
    $sendMail->mail($message);

  }

}
