<?php

declare(strict_types=1);

namespace Drupal\sg_goal\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a search nonprofit goal pillar block.
 *
 * @Block(
 *   id = "sg_goal_search_group_hub_goal_pillar",
 *   admin_label = @Translation("Search Group Hub Goal Pillar"),
 *   category = @Translation("SG Goal"),
 * )
 */
final class SearchGroupHubGoalPillarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('token')
    );
  }

  /**
   * Create a pillar block.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Utility\Token $token
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'body' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#default_value' => $this->configuration['body'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['body'] = $form_state->getValue('body');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build['content'] = [
      '#markup' => Markup::create($this->token->replace($this->configuration['body'])),
      '#cache' => [
        'tags' => ['config:sg_setup.activity'],
      ],
    ];
    return $build;
  }

}
