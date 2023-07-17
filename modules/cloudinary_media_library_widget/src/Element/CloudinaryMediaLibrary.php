<?php

namespace Drupal\cloudinary_media_library_widget\Element;

use Cloudinary\Configuration\Configuration;
use Drupal\Core\Link;
use Drupal\Core\Render\Element\Textfield;

/**
 * Provides a form element to display a textfield integrated with cloudinary.
 *
 * Properties:
 * - #resource_type: Limits uploaded files to the specified type.
 *    By default, all resource types are allowed.
 * - #preview: Whether to display preview image above the field.
 *    By default, the preview is not displayed to have maximum performance.
 *
 * Usage Example:
 * @code
 * $form['image'] = [
 *   '#type' => 'cloudinary_media_library',
 *   '#resource_type' => 'image',
 *   '#title' => t('Image'),
 *   '#preview' => TRUE,
 *   '#description' => t('Choose an image from cloudinary media library'),
 * ];
 * @endcode
 *
 * @FormElement("cloudinary_media_library")
 */
class CloudinaryMediaLibrary extends Textfield {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $info['#theme'] = 'input__textfield__cloudinary';
    $info['#pre_render'][] = [static::class, 'preRenderCloudinary'];
    $info['#resource_type'] = 'auto';
    $info['#preview'] = FALSE;
    $info['#placeholder'] = 'cloudinary:<resource_type>:<delivery_type>:<public_id>';

    return $info;
  }

  /**
   * Validate cloudinary connection.
   */
  public static function validateConnection() {
    try {
      Configuration::instance()->validate();
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addWarning(t('You have not provided a valid connection to your Cloudinary instance. Follow @link to set up a connection.', [
        '@link' => Link::createFromRoute(t('this link'), 'cloudinary_sdk.settings')->toString(),
      ]));
    }
  }

  /**
   * Prepares a #type 'cloudinary_media_library' render element.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderCloudinary($element) {
    static::validateConnection();

    $element['#attached']['library'][] = 'cloudinary_media_library_widget/form';
    $element['#attached']['drupalSettings']['cloudinary_media_library'] = self::baseDrupalSettings();

    $element['#attributes']['data-resource-type'] = $element['#resource_type'];
    $class = ['form-cloudinary'];

    // Mark input with preview.
    if ($element['#preview']) {
      $class[] = 'has-preview';

      if ($element['#value']) {
        /** @var \Drupal\cloudinary_media_library_widget\AssetGeneratorInterface $generator */
        $generator = \Drupal::service('cloudinary_media_library_widget.asset_generator');
        $preview = $generator->generatePreviewImageUrl($element['#value']);

        if ($preview) {
          $element['#attributes']['data-preview-image'] = $preview;
        }
      }
    }

    static::setAttributes($element, $class);

    return $element;
  }

  /**
   * Build base drupalSettings required for the cloudinary media widget.
   *
   * @return array
   *   List of Drupal settings.
   */
  public static function baseDrupalSettings() {
    $sdk_config = \Drupal::config('cloudinary_sdk.settings');
    $widget_config = \Drupal::config('cloudinary_media_library_widget.settings');

    $settings = [
      'cloud_name' => $sdk_config->get('cloudinary_sdk_cloud_name'),
      'api_key' => $sdk_config->get('cloudinary_sdk_api_key'),
      'use_saml' => $widget_config->get('cloudinary_saml_auth'),
    ];

    $starting_folder = $widget_config->get('cloudinary_starting_folder');

    if ($starting_folder !== '/') {
      $settings['starting_folder'] = $starting_folder;
    }

    return $settings;
  }

}
