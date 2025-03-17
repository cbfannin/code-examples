<?php

declare(strict_types=1);

namespace Drupal\sg_goal;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of goal type entities.
 *
 * @see \Drupal\sg_goal\Entity\GoalType
 */
final class GoalTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Title');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();

    $build['table']['#empty'] = $this->t(
      'No goal types available. <a href=":link">Add goal type</a>.',
      [':link' => Url::fromRoute('entity.goal_type.add_form')->toString()],
    );

    return $build;
  }

}
