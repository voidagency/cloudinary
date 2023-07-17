<?php

namespace Drupal\cloudinary_media_library_widget\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an CloudinaryMedia annotation.
 *
 * @Annotation
 */
class CloudinaryMedia extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The asset type from Cloudinary.
   *
   * @var string
   */
  public $resource_type;

}
