<?php

namespace Drupal\cloudinary_media_library_widget;

use Cloudinary\Tag\BaseTag;

/**
 * Define an interface for asset generator service.
 */
interface AssetGeneratorInterface {

  /**
   * Whether the value from custom form element is applicable for further usage.
   *
   * @param string $value
   *   The value from custom form element.
   *
   * @return bool
   *   TRUE if the value is applicable and can be used by the service.
   */
  public function isApplicable(string $value): bool;

  /**
   * Parse string value into array.
   *
   * @param string $value
   *   The value from custom form element.
   *
   * @return array
   *   The parsed value.
   */
  public function parseValue(string $value): array;

  /**
   * Generate preview image based on value from the form element.
   *
   * @param string $value
   *   The value from custom form element.
   *
   * @return string|null
   *   Returns an absolute preview image url or NULL if not applicable.
   */
  public function generatePreviewImageUrl(string $value): ?string;

  /**
   * Render asset based on value from the form element.
   *
   * @param string $value
   *   The value from custom form element.
   * @param string|null $raw_transformation
   *   Optional raw transformation to be applied to the asset.
   *
   * @return array|null
   *   Returns a renderable array of asset or NULL if nothing to render.
   */
  public function renderAsset(string $value, string $raw_transformation = NULL): ?array;

  /**
   * Get base tag to manipulate and render later.
   *
   * @param string $value
   *   The value from custom form element.
   *
   * @return \Cloudinary\Tag\BaseTag|null
   *   The cloudinary base tag like VideoTag or ImageTag.
   */
  public function getBaseTag(string $value): ?BaseTag;

}
