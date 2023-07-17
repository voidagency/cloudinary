<?php

namespace Drupal\cloudinary_sdk\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Init cloudinary on each request.
 */
class InitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  /**
   * Init cloudinary instance.
   */
  public function onEvent(RequestEvent $event) {
    cloudinary_sdk_init();
  }

}
