<?php

namespace Drupal\cloudinary_media_library_widget\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Build an example form to show how to use cloudinary form element.
 */
class CloudinaryElementExampleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cloudinary_form_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['image'] = [
      '#type' => 'cloudinary_media_library',
      '#resource_type' => 'image',
      '#title' => $this->t('Image'),
      '#description' => $this->t('Choose an image from cloudinary media library.'),
    ];

    $form['video'] = [
      '#type' => 'cloudinary_media_library',
      '#resource_type' => 'video',
      '#title' => $this->t('Video'),
      '#description' => $this->t('Choose a video from cloudinary media library.'),
    ];

    $form['document'] = [
      '#type' => 'cloudinary_media_library',
      '#resource_type' => 'raw',
      '#title' => $this->t('Document'),
      '#description' => $this->t('Choose a document from cloudinary media library.'),
    ];

    $form['assets'] = [
      '#type' => 'cloudinary_media_library',
      '#title' => $this->t('Assets'),
      '#description' => $this->t('Choose any asset from cloudinary media library.'),
    ];

    $form['assets_with_preview'] = [
      '#type' => 'cloudinary_media_library',
      '#title' => $this->t('Assets with Preview'),
      '#preview' => TRUE,
      '#description' => $this->t('Choose any asset from cloudinary media library.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
