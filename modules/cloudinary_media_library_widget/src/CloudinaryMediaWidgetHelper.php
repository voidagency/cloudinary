<?php

namespace Drupal\cloudinary_media_library_widget;

/**
 * Define a helper class for cloudinary media library widget.
 */
class CloudinaryMediaWidgetHelper implements CloudinaryMediaWidgetHelperInterface {

  /**
   * {@inheritdoc}
   */
  public static function convertStringOptionsToArray(string $value): array {
    $value = explode("\r\n", $value);
    $value = array_map('trim', $value);

    return array_filter($value);
  }

  /**
   * {@inheritdoc}
   */
  public static function convertArrayOptionsToString(array $options): string {
    return implode("\r\n", $options);
  }

}
