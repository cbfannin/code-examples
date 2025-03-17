<?php

declare(strict_types=1);

namespace Drupal\sg_goal\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Goal type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "goal_type",
 *   label = @Translation("Goal type"),
 *   label_collection = @Translation("Goal types"),
 *   label_singular = @Translation("goal type"),
 *   label_plural = @Translation("goals types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count goals type",
 *     plural = "@count goals types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\sg_goal\Form\GoalTypeForm",
 *       "edit" = "Drupal\sg_goal\Form\GoalTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\sg_goal\GoalTypeListBuilder",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer goal types",
 *   bundle_of = "goal",
 *   config_prefix = "goal_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/goal_types/add",
 *     "edit-form" = "/admin/structure/goal_types/manage/{goal_type}",
 *     "delete-form" = "/admin/structure/goal_types/manage/{goal_type}/delete",
 *     "collection" = "/admin/structure/goal_types",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   },
 * )
 */
final class GoalType extends ConfigEntityBundleBase {

  /**
   * The machine name of this goal type.
   */
  protected string $id;

  /**
   * The human-readable name of the goal type.
   */
  protected string $label;

}
