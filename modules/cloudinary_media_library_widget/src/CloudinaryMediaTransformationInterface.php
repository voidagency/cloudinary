<?php

namespace Drupal\cloudinary_media_library_widget;

use Drupal\media\MediaInterface;

/**
 * Define an interface for Cloudinary media transformations.
 */
interface CloudinaryMediaTransformationInterface {

  /**
   * Get the cloudinary URI with applied transformations.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity.
   *
   * @return string
   *   The Cloudinary URI.
   */
  public function getCloudinaryTransformedUri(MediaInterface $media): string;

  /**
   * Build breakpoint sources for the cloudinary image.
   *
   * @param string $uri
   *   The URI of the image file.
   * @param array $breakpoints
   *   The list of breakpoints. Example [100, 200, 300, 400].
   *
   * @return array
   *   An array of values for the srcset attribute.
   */
  public function buildBreakpointSources(string $uri, array $breakpoints): array;

}
