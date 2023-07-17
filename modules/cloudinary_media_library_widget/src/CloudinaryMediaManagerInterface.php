<?php

namespace Drupal\cloudinary_media_library_widget;

use Drupal\cloudinary_media_library_widget\Plugin\CloudinaryMediaPluginInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Define interface for cloudinary media plugin manager.
 */
interface CloudinaryMediaManagerInterface extends PluginManagerInterface {

  /**
   * Create instance by the resource type.
   *
   * @param string $type
   *   The resource type.
   * @param array $configuration
   *   The additional plugin configuration.
   *
   * @return \Drupal\cloudinary_media_library_widget\Plugin\CloudinaryMediaPluginInterface
   *   The cloudinary media plugin.
   */
  public function createInstanceByType(string $type, array $configuration = []): CloudinaryMediaPluginInterface;

}
