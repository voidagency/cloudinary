<?php

namespace Drupal\cloudinary_media_library_widget\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * Plugin implementation of the 'cloudinary_image' formatter.
 *
 * @FieldFormatter(
 *   id = "cloudinary_image",
 *   label = @Translation("Cloudinary image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class CloudinaryImageFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['image_style']['#access'] = FALSE;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    foreach ($elements as &$element) {
      $element['#theme'] = 'cloudinary_image_formatter';
    }

    // Invalidate cache if we update configs like default transformations.
    $elements['#cache']['tags'][] = 'config:cloudinary_media_library_widget.settings';

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getTargetBundle() === 'cloudinary_image';
  }

}
