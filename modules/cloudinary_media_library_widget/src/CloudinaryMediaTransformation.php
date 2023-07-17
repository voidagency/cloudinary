<?php

namespace Drupal\cloudinary_media_library_widget;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * Apply transformations to Cloudinary media files.
 */
class CloudinaryMediaTransformation implements CloudinaryMediaTransformationInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Construct the CloudinaryMediaService.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getCloudinaryTransformedUri(MediaInterface $media): string {
    $default_transformations = $this->configFactory
      ->get('cloudinary_media_library_widget.settings')
      ->get('cloudinary_image_optimizations');

    $custom_transformation = $media->get('field_cloudinary_transformation')->value ?? '';
    $transformations = implode('/', array_filter([
      $default_transformations,
      $custom_transformation,
    ]));

    // Build the Cloudinary URI with applied transformations.
    $file = $media->get('field_media_cloudinary_image')->entity;
    assert($file instanceof FileInterface);

    $file_uri = $file->getFileUri();

    // Return original file uri if no default transformations found.
    if (!$transformations) {
      return $file_uri;
    }

    [$schema, $uri] = explode('://', $file_uri, 2);

    // Fix asset version in the file uri to easily detect transformations
    // inside the uri.
    return "{$schema}://{$transformations}/v1/{$uri}";
  }

  /**
   * {@inheritdoc}
   */
  public function buildBreakpointSources(string $uri, array $breakpoints): array {
    sort($breakpoints);
    $resource_type = $delivery_type = NULL;
    $raw_transformation = '';
    $srcset = [];

    [$schema, $source] = explode('://', $uri, 2);

    if (preg_match('/^(.+)\/v1\/(.+)\.(.+)$/', $source, $matches)) {
      $raw_transformation = $matches[1];
      $source = "{$matches[2]}.{$matches[3]}";
    }

    if (preg_match('/^(image|video|raw):(upload|private|authenticated):([^:]*)(:(.+))?$/', $source, $matches)) {
      $resource_type = $matches[1];
      $delivery_type = $matches[2];
      $source = $matches[3];
      $raw_transformation = $matches[5] ?? '';
    }

    foreach ($breakpoints as $breakpoint) {
      $image_transformation = implode('/', array_filter([
        $raw_transformation,
        "w_{$breakpoint},c_scale",
      ]));

      // Fix version to support image transformation in the uri.
      $responsive_uri = isset($resource_type)
        ? "{$schema}://{$resource_type}:{$delivery_type}:{$source}:{$image_transformation}"
        : "{$schema}://{$image_transformation}/v1/{$source}";

      $srcset[] = [
        'uri' => $responsive_uri,
        'width' => "{$breakpoint}w",
      ];
    }

    return $srcset;
  }

}
