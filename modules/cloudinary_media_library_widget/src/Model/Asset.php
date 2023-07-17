<?php

namespace Drupal\cloudinary_media_library_widget\Model;

use Drupal\cloudinary_media_library_widget\Model\Context\Custom;

/**
 * Cloudinary asset.
 */
class Asset {

  /**
   * Public id.
   *
   * @var string
   */
  protected $public_id;

  /**
   * Resource type.
   *
   * @var string
   */
  protected $resource_type;

  /**
   * Type.
   *
   * @var string
   */
  protected $type;

  /**
   * Format.
   *
   * @var string
   */
  protected $format;

  /**
   * Version.
   *
   * @var string
   */
  protected $version;

  /**
   * Url.
   *
   * @var string
   */
  protected $url;

  /**
   * Secure url.
   *
   * @var string
   */
  protected $secure_url;

  /**
   * Width.
   *
   * @var int
   */
  protected $width;

  /**
   * Height.
   *
   * @var int
   */
  protected $height;

  /**
   * Bytes.
   *
   * @var int
   */
  protected $bytes;

  /**
   * Duration
   *
   * @var int|NULL
   */
  protected $duration;

  /**
   * Tags.
   *
   * @var array
   */
  protected $tags;

  /**
   * Metadata.
   *
   * @var array
   */
  protected $metadata;

  /**
   * Created at.
   *
   * @var string
   */
  protected $created_at;

  /**
   * Access mode.
   *
   * @var string
   */
  protected $access_mode;

  /**
   * Created by.
   *
   * @var string|NULL
   */
  protected $created_by;

  /**
   * Uploaded by.
   *
   * @var string|NULL
   */
  protected $uploaded_by;

  /**
   * Custom context.
   *
   * @var Custom|NULL
   */
  protected $denormalizedCustom = NULL;

  /**
   * Image derivations and transformations.
   *
   * @var array
   */
  protected $derived;

  /**
   * Get public id.
   *
   * @return string
   */
  public function getPublicId(): string {
    return $this->public_id;
  }

  /**
   * Get format.
   *
   * @return null|string
   */
  public function getFormat(): ?string {
    return $this->format;
  }

  /**
   * Get bytes.
   *
   * @return int
   */
  public function getBytes(): int {
    return $this->bytes;
  }

  /**
   * Set Custom context.
   *
   * @param \Drupal\cloudinary_media_library_widget\Model\Context\Custom $custom
   *   Custom context.
   *
   * @return void
   */
  public function setDenormalizedCustom(Custom $custom): void {
    $this->denormalizedCustom = $custom;
  }

  /**
   * Get custom context.
   *
   * @return \Drupal\cloudinary_media_library_widget\Model\Context\Custom|null
   *   Custom context.
   */
  public function getCustomContext(): ?Custom {
    return $this->denormalizedCustom;
  }

  /**
   * Get applied image transformations.
   *
   * @return string
   *   The applied image transformations.
   */
  public function getDerivedTransformations(): string {
    if (is_array($this->derived)) {
      $derived = current($this->derived);
      return $derived['raw_transformation'] ?? '';
    }

    return '';
  }

  /**
   * Get asset URI.
   *
   * @param \Drupal\cloudinary_media_library_widget\Model\Asset $asset
   *   Cloudinary asset.
   *
   * @return string
   *   Asset URI.
   */
  public function getAssetUri(Asset $asset): string {
    $suffix = '';

    if ($format = $asset->getFormat()) {
      $suffix = ".{$format}";
    }

    return 'cloudinary://' . $asset->getPublicId() . $suffix;
  }

  /**
   * Get the resource type.
   *
   * @return string
   *   The resource type of asset.
   */
  public function getResourceType(): string {
    return $this->resource_type;
  }

  /**
   * Get Asset secure URL.
   *
   * @return string
   *   The URL as string.
   */
  public function getSecureUrl(): string {
    return $this->secure_url;
  }

}
