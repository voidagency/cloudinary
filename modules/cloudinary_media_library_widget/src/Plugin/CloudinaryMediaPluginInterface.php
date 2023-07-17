<?php

namespace Drupal\cloudinary_media_library_widget\Plugin;

use Drupal\cloudinary_media_library_widget\Model\Asset;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\media\MediaInterface;

/**
 * Defines the interface for Cloudinary media plugins.
 *
 * @package Drupal\session_inspector\Plugin
 */
interface CloudinaryMediaPluginInterface extends PluginInspectionInterface {

  /**
   * Find Drupal media entity.
   *
   * @param \Drupal\cloudinary_media_library_widget\Model\Asset $asset
   *   Cloudinary asset.
   * @param string $bundle
   *   Media entity bundle.
   *
   * @return \Drupal\media\MediaInterface|null
   *   Drupal media entity.
   */
  public function findMedia(Asset $asset, string $bundle): ?MediaInterface;

  /**
   * Create media entity.
   *
   * @param \Drupal\cloudinary_media_library_widget\Model\Asset $asset
   *   Cloudinary asset.
   * @param string $bundle
   *   Media entity bundle.
   *
   * @return \Drupal\media\MediaInterface
   *   Cloudinary media entity.
   */
  public function createMedia(Asset $asset, string $bundle): MediaInterface;

}
