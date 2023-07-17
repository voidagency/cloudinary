<?php

namespace Drupal\cloudinary_media_library_widget;

/**
 * Define an interface for the helper class.
 */
interface CloudinaryMediaWidgetHelperInterface {

  /**
   * Convert a textarea compatible string into array of options.
   *
   * @param string $value
   *   The value from the textarea.
   *
   * @return array
   *   The transformed array options.
   */
  public static function convertStringOptionsToArray(string $value): array;

  /**
   * Convert an array of options to a textarea compatible string.
   *
   * @param array $options
   *   The options to convert.
   *
   * @return string
   *   The transformed string.
   */
  public static function convertArrayOptionsToString(array $options): string;

}
