<?php

namespace Drupal\ys_custom_events\EventSubscriber;

use Drupal\scheduler\SchedulerEvent;
use Drupal\scheduler\SchedulerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityTypeSubscriber.
 *
 * @package Drupal\ys_custom_events\EventSubscriber
 */
class PublishNodeEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      SchedulerEvents::PUBLISH => 'onNodePublish',
      SchedulerEvents::UNPUBLISH => 'onNodeUnpublish',
    ];
  }

  /**
   * React to a node being published.
   *
   * @param \Drupal\scheduler\SchedulerEvent $event
   *   Publish node event.
   */
  public function onNodePublish(SchedulerEvent $event) {
    $node = $event->getNode();
    $nid = $node->id();
    ys_custom_events_mail_template($nid, 'publish');
  }

  /**
   * React to a node being unpublished.
   *
   * @param \Drupal\scheduler\SchedulerEvent $event
   *   Unpublish node event.
   */
  public function onNodeUnpublish(SchedulerEvent $event) {
    $node = $event->getNode();
    $nid = $node->id();
    ys_custom_events_mail_template($nid, 'unpublish');
  }

}
