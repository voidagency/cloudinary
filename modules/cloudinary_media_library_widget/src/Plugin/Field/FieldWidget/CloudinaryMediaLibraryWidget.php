<?php

namespace Drupal\cloudinary_media_library_widget\Plugin\Field\FieldWidget;

use Drupal\cloudinary_media_library_widget\Element\CloudinaryMediaLibrary;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_library\Plugin\Field\FieldWidget\MediaLibraryWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'cloudinary_media_library_widget' widget.
 *
 * @FieldWidget(
 *   id = "cloudinary_media_library_widget",
 *   label = @Translation("Cloudinary media library"),
 *   description = @Translation("Allows you to select cloudinary assets."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE,
 * )
 *
 * @internal
 *   Plugin classes are internal.
 */
class CloudinaryMediaLibraryWidget extends MediaLibraryWidget {

  /**
   * The Cloudinary media manager.
   *
   * @var \Drupal\cloudinary_media_library_widget\CloudinaryMediaManagerInterface
   */
  protected $cloudinaryMediaManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->cloudinaryMediaManager = $container->get('cloudinary_media_library_widget.manager.cloudinary_media');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'resource_type' => 'image',
    ] + parent::defaultSettings();
  }

  /**
   * Get resource type options.
   *
   * @return array
   *   List of options.
   */
  protected function getResourceTypeOptions() {
    $options = [];

    foreach ($this->cloudinaryMediaManager->getDefinitions() as $definition) {
      $options[$definition['resource_type']] = $definition['label'];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['resource_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Default resource'),
      '#options' => $this->getResourceTypeOptions(),
      '#default_value' => $this->getSetting('resource_type'),
      '#description' => $this->t('Select the default resource type preselected on Cloudinary media widget.'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $options = $this->getResourceTypeOptions();
    $summary[] = $this->t('Cloudinary resource type: @resource', [
      '@resource' => $options[$this->getSetting('resource_type')],
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    CloudinaryMediaLibrary::validateConnection();

    $bundle = current($this->getAllowedMediaTypeIdsSorted());

    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#attributes']['class'][] = 'js-cloudinary-library-widget';
    $element['#attributes']['data-field-name'] = $this->fieldDefinition->getName();
    $element['media_library_selection']['#attributes']['class'][] = 'js-cloudinary-library-selection';
    $element['media_library_update_widget']['#attributes']['class'][] = 'js-cloudinary-library-update-widget';

    $element['open_button'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['js-cloudinary-library-open-button']],
      '#attached' => [
        'library' => ['cloudinary_media_library_widget/widget'],
        'drupalSettings' => $this->getDrupalJavascriptSettings($bundle),
      ],
    ];

    return $element;
  }

  /**
   * Get drupal javascript settings.
   *
   * @param string $bundle
   *   Media bundle.
   *
   * @return array
   *   Cloudinary js settings.
   */
  protected function getDrupalJavascriptSettings(string $bundle): array {
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();

    $settings = CloudinaryMediaLibrary::baseDrupalSettings() + [
      'bundle' => $bundle,
      'multiple' => $cardinality != 1,
      'resource_type' => $this->getSetting('resource_type'),
    ];

    if ($cardinality != FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $settings['max_files'] = $cardinality;
    }

    return [
      'cloudinary_media_library_widget' => [
        $this->fieldDefinition->getName() => $settings,
      ],
    ];
  }

}
