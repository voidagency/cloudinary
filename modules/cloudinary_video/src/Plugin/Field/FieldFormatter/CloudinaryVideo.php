<?php

namespace Drupal\cloudinary_video\Plugin\Field\FieldFormatter;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\video_embed_field\Plugin\Field\FieldFormatter\Video;

/**
 * Plugin implementation of the cloudinary video field formatter.
 *
 * @FieldFormatter(
 *   id = "cloudinary_video",
 *   label = @Translation("Cloudinary Video"),
 *   field_types = {
 *     "video_embed_field"
 *   }
 * )
 */
class CloudinaryVideo extends Video {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings() + [
      'player' => \Drupal::config('cloudinary_video')->get('cloudinary_default_player') ?? 'html5',
      'cloudinary_player_config' => "{\r\n\r\n}",
      'html5_player_config' => '',
      'raw_transformation' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['player'] = [
      '#type' => 'radios',
      '#title' => $this->t('Override player'),
      '#options' => [
        'html5' => $this->t('HTML 5 Player'),
        'cloudinary' => $this->t('Cloudinary Video player'),
      ],
      '#default_value' => $this->getSetting('player'),
      '#description' => $this->t('Using different player than default is not currently supported.'),
      '#required' => TRUE,
    ];

    $field_name = $this->fieldDefinition->getName();
    $player_input = sprintf('input[name="fields[%s][settings_edit_form][settings][player]"]', $field_name);

    $element['html5_player_config'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Override HTML5 player configuration'),
      '#default_value' => $this->getSetting('html5_player_config'),
      '#description' => $this->t('Provide list of attributes separated by space, e.g <strong>controls loop preload="auto"</strong>. Check available player attributes @here.', [
        '@here' => Link::fromTextAndUrl(
          $this->t('here'),
          Url::fromUri('https://www.w3schools.com/tags/tag_video.asp')->setOption('attributes', ['target' => '_blank'])
        )->toString(),
        '@link' => Link::fromTextAndUrl(
          $this->t('Video Player studio'),
          Url::fromUri('https://studio.cloudinary.com')->setOption('attributes', ['target' => '_blank'])
        )->toString(),
      ]),
      '#states' => [
        'visible' => [
          $player_input => ['value' => 'html5'],
        ],
      ],
    ];

    $element['cloudinary_player_config'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Override Cloudinary player configuration (JSON)'),
      '#default_value' => $this->getSetting('cloudinary_player_config'),
      '#description' => $this->t('Check available player options @here, or use the @link to try out different configurations.', [
        '@here' => Link::fromTextAndUrl(
          $this->t('here'),
          Url::fromUri('https://cloudinary.com/documentation/video_player_api_reference#configuration_options')->setOption('attributes', ['target' => '_blank'])
        )->toString(),
        '@link' => Link::fromTextAndUrl(
          $this->t('Video Player studio'),
          Url::fromUri('https://studio.cloudinary.com')->setOption('attributes', ['target' => '_blank'])
        )->toString(),
      ]),
      '#element_validate' => [[static::class, 'playerConfigValidate']],
      '#states' => [
        'visible' => [
          $player_input => ['value' => 'cloudinary'],
        ],
      ],
    ];

    $element['raw_transformation'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Override optimizations for video'),
      '#default_value' => $this->getSetting('raw_transformation'),
      '#rows' => 2,
      '#description' => $this->t('Represents a different component (separated by a "/"), for example: ar_1:1,c_fill,g_auto,w_300/e_blur:50/r_max. Check official @docs to get more details.', [
        '@docs' => Link::fromTextAndUrl(
          $this->t('docs'),
          Url::fromUri('https://cloudinary.com/documentation/video_manipulation_and_delivery')->setOption('attributes', ['target' => '_blank'])
        )->toString(),
      ]),
    ];

    return $element;
  }

  /**
   * Check if cloudinary player config is valid.
   *
   * @param array $element
   *   The json form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the field formatter settings form.
   */
  public static function playerConfigValidate($element, FormStateInterface $form_state) {
    try {
      json_decode($element['#value'], NULL, 512, JSON_THROW_ON_ERROR);
    }
    catch (\Exception $e) {
      $form_state->setError($element, t('Invalid JSON provided for player configuration.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Player: @player.', [
      '@player' => $this->getSetting('player'),
    ]);

    return $summary;
  }

  /**
   * Get player configs based on the player.
   *
   * @return array|string
   *   The player configs.
   */
  protected function getPlayerConfig() {
    return $this->getSetting('player') === 'cloudinary'
      ? Json::decode($this->getSetting('cloudinary_player_config'))
      : $this->getSetting('html5_player_config');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $player_config = $this->getPlayerConfig();
    $raw_transformation = $this->getSetting('raw_transformation');

    foreach ($elements as &$element) {
      if (!isset($element['children'])) {
        continue;
      }

      $element['children']['#player_type'] = $this->getSetting('player');

      // Apply custom raw transformation.
      if ($raw_transformation) {
        $element['children']['#raw_transformation'] = $raw_transformation;
      }

      switch ($element['children']['#player_type']) {
        case 'cloudinary':
          $element['children']['#player'] = $player_config;
          break;

        case 'html5':
          $attributes = $element['children']['#attributes'] ?? [];

          foreach (explode(' ', $player_config) as $attribute) {
            $data = explode('=', $attribute);
            $attributes[$data[0]] = $data[1] ?? $data[0];
          }

          $element['children']['#attributes'] = $attributes;
          break;
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $providers = $field_definition->getSetting('allowed_providers');

    return !empty($providers['cloudinary']);
  }

}
