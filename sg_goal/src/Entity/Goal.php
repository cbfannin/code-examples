<?php

declare(strict_types=1);

namespace Drupal\sg_goal\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\sg_goal\GoalInterface;

/**
 * Defines the goal entity class.
 *
 * @ContentEntityType(
 *   id = "goal",
 *   label = @Translation("Goal"),
 *   label_collection = @Translation("Goals"),
 *   label_singular = @Translation("goal"),
 *   label_plural = @Translation("goals"),
 *   label_count = @PluralTranslation(
 *     singular = "@count goal",
 *     plural = "@count goals",
 *   ),
 *   bundle_label = @Translation("Goal type"),
 *   handlers = {
 *     "list_builder" = "Drupal\sg_goal\GoalListBuilder",
 *     "views_data" = "Drupal\sg_goal\GoalViewsData",
 *     "access" = "Drupal\sg_goal\GoalAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\sg_goal\Form\GoalForm",
 *       "edit" = "Drupal\sg_goal\Form\GoalForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\sg_goal\Routing\GoalHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "goal",
 *   admin_permission = "administer goal types",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/goal",
 *     "add-form" = "/goal/add/{goal_type}",
 *     "add-page" = "/goal/add",
 *     "canonical" = "/goal/{goal}",
 *     "edit-form" = "/goal/{goal}/edit",
 *     "delete-form" = "/goal/{goal}/delete",
 *     "delete-multiple-form" = "/admin/content/goal/delete-multiple",
 *   },
 *   bundle_entity_type = "goal_type",
 *   field_ui_base_route = "entity.goal_type.edit_form",
 * )
 */
final class Goal extends ContentEntityBase implements GoalInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the goal was last edited.'))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the goal was created.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'timestamp',
      ]);

    $fields['date_range'] = BaseFieldDefinition::create('smartdate')
      ->setLabel(t('Date range'))
      ->setDefaultValue([
        'default_date_type' => 'next_hour',
        'default_date' => '',
        'default_duration_increments' => "0 | No End Time\r\n30\r\n60|1 hour\r\n90\r\n120|2 hours\r\ncustom",
        'default_duration' => '1440',
        'allday' => '0',
      ])
      ->setRequired(TRUE)
      ->setDescription(t('The active date range for the Goal.'))
      ->setDisplayOptions('form', [
        'type' => 'smartdate_only',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'smartdate_only',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['related_organizations'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Related organizations'))
      ->setDescription(t('References to Group Hub and Nonprofit organizations.'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler', 'default:node')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'group_hub' => 'group_hub',
          'nonprofit' => 'nonprofit',
        ],
        'auto_create' => FALSE,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 11,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 11,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 10,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
