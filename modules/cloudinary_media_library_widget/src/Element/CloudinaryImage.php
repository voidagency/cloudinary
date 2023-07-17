<?php

namespace Drupal\cloudinary_media_library_widget\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a render element to render cloudinary image.
 *
 * Properties:
 * - #public_id: Public ID of the asset to render.
 * - #delivery_type: Delivery type of the asset.
 * - #alt: Alt attribute of the image.
 * - #title: Title attribute of the image.
 * - #breakpoints: Whether to apply breakpoints if it's configured.
 * - #default_transformation: Whether to apply default image transformation.
 * - #attributes: Apply custom attributes to the image.
 * - #raw_transformation: Optional string of URL transformation.
 *     e.g. 'w_400,c_pad'.
 *
 * Usage Example:
 * @code
 * $build['image'] = [
 *   '#type' => 'cloudinary_image',
 *   '#public_id' => 'cld-sample',
 *   '#breakpoints' => FALSE,
 *   '#alt' => t('Sample image'),
 *   '#default_transformation' => FALSE,
 *   '#raw_transformation' => 'f_auto/q_auto/c_fill,w_400',
 *   '#attributes' => ['class' => ['demo-image']],
 * ];
 * @endcode
 *
 * @RenderElement("cloudinary_image")
 */
class CloudinaryImage extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#theme' => 'image',
      '#resource_type' => 'image',
      '#breakpoints' => TRUE,
      '#delivery_type' => 'upload',
      '#default_transformation' => TRUE,
      '#raw_transformation' => NULL,
      '#pre_render' => [
        [get_class($this), 'preRenderImageElement'],
      ],
    ];
  }

  /**
   * Cloudinary image element pre render callback.
   *
   * @param array $element
   *   An associative array containing the properties of the image element.
   *
   * @return array
   *   The modified element.
   */
  public static function preRenderImageElement($element) {
    $widget_config = \Drupal::config('cloudinary_media_library_widget.settings');
    $uri = "cloudinary://{$element['#resource_type']}:{$element['#delivery_type']}:{$element['#public_id']}";

    // Apply default transformation.
    $transformations = [];
    if ($element['#default_transformation']) {
      $default_transformations = $widget_config->get('cloudinary_image_optimizations');

      if ($default_transformations) {
        $element['#cache']['tags'] = $widget_config->getCacheTags();
        $transformations[] = $default_transformations;
      }
    }

    if (isset($element['#raw_transformation'])) {
      $transformations[] = $element['#raw_transformation'];
    }

    $transformations = implode('/', array_filter($transformations));
    if ($transformations) {
      $uri .= ":{$transformations}";
    }

    $element['#uri'] = $uri;

    if ($breakpoints = $widget_config->get('cloudinary_responsive_breakpoints')) {
      $element['#sizes'] = '100vw';

      /** @var \Drupal\cloudinary_media_library_widget\CloudinaryMediaTransformationInterface $transformation */
      $transformation = \Drupal::service('cloudinary_media_library_widget.media_transformation');
      $element['#srcset'] = $transformation->buildBreakpointSources($uri, $breakpoints);
    }

    return $element;
  }

}
