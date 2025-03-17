<?php

/**
 * @file
 * Token functionality for the sg_goal module.
 */

use Drupal\Core\Render\Markup;

/**
 * Implements hook_token_info().
 */
function sg_goal_token_info() {
  return [
    'tokens' => [
      'site' => [
        'nonprofit_goal_promo' => [
          'name' => t('Nonprofit Goal promo text'),
          'description' => t("Text to appear below the headings on the Nonprofit Goal screen"),
        ],
        'nonprofit_goal_sponsors' => [
          'name' => t('Nonprofit Goal sponsors'),
          'description' => t("Sponsor logos to appear below the headings on the Nonprofit Goal search page"),
        ],
        'group_hub_goal_promo' => [
          'name' => t('Group Hub Goal promo text'),
          'description' => t("Text to appear below the headings on the Group Hub Goal screen"),
        ],
        'group_hub_goal_sponsors' => [
          'name' => t('Nonprofit Goal sponsors'),
          'description' => t("Sponsor logos to appear below the headings on the Nonprofit Goal search page"),
        ],
      ],
      'goal' => [
        'add-donation-goal-button' => [
          'name' => t('Add a donation goal.'),
          'description' => "A button to add a donation goal.",
        ],
        'add-volunteers-goal-button' => [
          'name' => t('Add a volunteers goal.'),
          'description' => "A button to add a volunteers goal.",
        ],
      ]
    ],
    'types' => [
      'goal' => [
        'name' => t('Goal'),
        'description' => t('Goal Tokens.'),
      ],
    ]
  ];
}

/**
 * Implements hook_tokens().
 *
 * @see sg_setup_tokens()
 */
function sg_goal_tokens($type, $tokens, array $data = [], array $options = []) {
  $replacements = [];
  $config = \Drupal::config('sg_setup.activity');
  if ($type == 'site') {
    foreach ($tokens as $name => $original) {
      if (in_array($name, [
        'nonprofit_goal_promo',
        'group_hub_goal_promo',
      ])) {
        $replacements[$original] = \Drupal::config('sg_setup.activity')->get($name);
      }
      elseif (in_array($name, [
        'nonprofit_goal_sponsors',
        'group_hub_goal_sponsors',
      ])) {
        // Yoinked from web/modules/custom/sg_setup/sg_setup.tokens.inc.
        $markup_text = NULL;
        for ($i = 1; $i <= 3; $i++) {
          $fids = $config->get("{$name}_logo_{$i}");
          if (!empty($fids)) {
            $fid = $fids[0];
            $replacements[$original] = "sanity test {$name} {$fid}";
            /** @var \Drupal\file\Entity\File|null */
            $file = \Drupal::entityTypeManager()
              ->getStorage('file')
              ->load($fid);
            if (!is_null($file)) {
              $url = $config->get("{$name}_url_{$i}");
              $markup_text .= "<a href='{$url}' hreflang='en'><img loading='lazy' src='{$file->createFileUrl()}' height='110' typeof='foaf:Image' class='mt-2'></a>";
            }
          }
        }
        if (!is_null($markup_text)) {
          $replacements[$original] = Markup::create("<div class='d-flex justify-content-around flex-column flex-sm-row'>{$markup_text}</div>");
        }
        else {
          $replacements[$original] = '';
        }
      }
    }
  }
  if ($type == 'goal') {
    foreach ($tokens as $name => $original) {
      if (
        str_starts_with($name, 'add-') &&
        preg_match('#/(\d+)/#', \Drupal::service('path.current')->getPath(), $m)
      ) {
        // Check if the nid is provided.
        if ($m[1] > 0) {
          // Button to add a donation or volunteer goal.
          $type = substr($name, 4, -12);
          $replacements[$original] = Markup::create('<a href="/goal/add/' . $type
            . '?edit[related_organizations][widget][0][target_id]=' . $m[1]
            . '" class="btn button btn-secondary">Add ' . ucfirst($type) . ' goal</a>');
        } else {
          $replacements[$original] = "No organization ID provided. Please edit a nonprofit or group hub first, then click the Goals tab.";
        }
      }
    }
  }
  return $replacements;
}
