<?php

namespace Drupal\cloudinary_media_library_widget\Model\Context;

/**
 * Contextual custom metadata.
 */
class Custom {

  /**
   * Description (alt)
   *
   * @var string|NULL
   */
  protected $alt = NULL;

  /**
   * Title (caption).
   *
   * @var string|NULL
   */
  protected $caption = NULL;

  /**
   * Get alt.
   *
   * @return string|null
   */
  public function getAlt(): ?string {
    return $this->alt;
  }

  /**
   * Get caption.
   *
   * @return string|null
   */
  public function getCaption(): ?string {
    return $this->caption;
  }

}
