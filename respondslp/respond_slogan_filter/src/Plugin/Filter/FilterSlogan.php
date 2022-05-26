<?php
/**
 * @file
 * Contains Drupal\respond_slogan_filter\Plugin\Filter\FilterSlogan
 */

namespace Drupal\respond_slogan_filter\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a filter to replace [slogan] with the site's slogan.
 *
 * @Filter(
 *   id = "filter_slogan",
 *   title = @Translation("Slogan Filter"),
 *   description = @Translation("Allow this text format to replace a [slogan] token with the site's slogan."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class FilterSlogan extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $slogan = $this->settings['slogan'] ? 'Because You Have More To Say.' : '';
    $replace = '<span class="slogan-filter">' . $this->t($slogan) . ' </span>';
    $new_text = str_replace('[slogan]', $replace, $text);

    $result = new FilterProcessResult($new_text);
    $result->setAttachments(array(
      'library' => array('respond_slogan_filter/respond_slogan'),
    ));
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['slogan'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show Slogan?'),
      '#default_value' => $this->settings['slogan'],
      '#description' => $this->t('Display the site slogan using the [slogan] token.'),
    );
    return $form;
  }
}