<?php

namespace Drupal\cloudinary_media_library_widget;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Tag\BaseTag;
use Cloudinary\Transformation\Resize;

/**
 * Define a service to work with custom value from cloudinary form element.
 */
class CloudinaryAssetGenerator implements AssetGeneratorInterface {

  /**
   * Get instance of cloudinary API.
   *
   * @return \Cloudinary\Cloudinary
   *   The cloudinary API.
   */
  protected function cloudinary() {
    return new Cloudinary(Configuration::instance());
  }

  /**
   * {@inheritdoc}
   */
  public function generatePreviewImageUrl(string $value): ?string {
    try {
      $info = $this->parseValue($value);
    }
    catch (\Exception $e) {
      return NULL;
    }

    switch ($info['resource_type']) {
      case 'image':
        return (string) $this->cloudinary()->image($info['public_id'])
          ->deliveryType($info['delivery_type'])
          ->resize(Resize::fill()->width(400))
          // Force extension to support PDF thumbnails.
          ->extension('jpeg')
          ->toUrl();

      case 'video':
        return (string) $this->cloudinary()->video($info['public_id'])
          ->resize(Resize::fill()->width(400))
          ->deliveryType($info['delivery_type'])
          ->extension('jpeg')
          ->toUrl();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function renderAsset(string $value, string $raw_transformation = NULL): ?array {
    try {
      $info = $this->parseValue($value);
    }
    catch (\Exception $e) {
      // Nothing to render here.
      return NULL;
    }

    switch ($info['resource_type']) {
      case 'image':
        return $this->renderImage($info, $raw_transformation);

      case 'video':
        return $this->renderVideo($info, $raw_transformation);
    }

    // Nothing to render.
    return NULL;
  }

  /**
   * Render image asset in Drupal way.
   *
   * @param array $info
   *   The parsed value from the form element.
   *
   * @return array
   *   Renderable array with image element build.
   */
  protected function renderImage(array $info, string $raw_transformation = NULL): array {
    return [
      '#type' => 'cloudinary_image',
      '#public_id' => $info['public_id'],
      '#delivery_type' => $info['delivery_type'],
      '#raw_transformation' => $raw_transformation,
    ];
  }

  /**
   * Render video asset in Drupal way.
   *
   * @param array $info
   *   The parsed value from the form element.
   *
   * @return array
   *   Renderable array with video element build.
   */
  protected function renderVideo(array $info, string $raw_transformation = NULL): array {
    return [
      '#type' => 'cloudinary_video',
      '#public_id' => $info['public_id'],
      '#delivery_type' => $info['delivery_type'],
      '#raw_transformation' => $raw_transformation,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(string $value): bool {
    return preg_match('/^cloudinary:(image|video|raw):(upload|private|authenticated):(.+)$/', $value) > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function parseValue(string $value): array {
    if (!$this->isApplicable($value)) {
      throw new \Exception('Could not parse the value.');
    }

    [$type, $resource_type, $delivery_type, $public_id] = explode(':', $value);

    return [
      'type' => $type,
      'resource_type' => $resource_type,
      'delivery_type' => $delivery_type,
      'public_id' => $public_id,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseTag(string $value): ?BaseTag {
    try {
      $info = $this->parseValue($value);
    }
    catch (\Exception $e) {
      // Nothing to render here.
      return NULL;
    }

    switch ($info['resource_type']) {
      case 'image':
        return $this->cloudinary()->imageTag($info['public_id']);

      case 'video':
        return $this->cloudinary()->videoTag($info['public_id']);
    }

    // Nothing to render.
    return NULL;
  }

}
