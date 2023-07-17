<?php

namespace Drupal\cloudinary_media_library_widget\Plugin\Exception;

/**
 * Cloudinary media exception.
 */
class CloudinaryMediaNotFoundException extends \Exception {

  /**
   * Construct a CloudinaryMediaNotFoundException exception.
   *
   * @param string $type
   *   The resource type that was not found.
   * @param string $message
   *   The exception message.
   * @param int $code
   *   The exception code.
   * @param \Exception|null $previous
   *   The previous throwable used for exception chaining.
   *
   * @see \Exception
   */
  public function __construct($type, $message = '', $code = 0, \Exception $previous = NULL) {
    if (empty($message)) {
      $message = sprintf("Cloudinary media with resource type '%s' was not found.", $type);
    }
    parent::__construct($message, $code, $previous);
  }

}
