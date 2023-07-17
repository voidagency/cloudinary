<?php

namespace Drupal\cloudinary_video\Element;

use Cloudinary\Tag\VideoTag;
use Cloudinary\Transformation\CommonTransformation;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\Markup;

/**
 * Provides a render element to render cloudinary video.
 *
 * Properties:
 * - #public_id: Public ID of the asset to render.
 * - #player_type: Override default player type.
 * - #player: Override default cloudinary player configuration.
 * - #responsive: Whether to display responsive video.
 * - #delivery_type: Delivery type of the asset.
 * - #sources: Override default sources.
 * - #default_transformation: Whether to apply default image transformation.
 * - #attributes: Apply custom attributes to the image.
 * - #raw_transformation: Optional string of URL transformation.
 *     e.g. 'e_progressbar:width_5'.
 *
 * Usage Example:
 * @code
 * $build['video'] = [
 *   '#type' => 'cloudinary_video',
 *   '#public_id' => 'samples/cld-sample-video',
 *   '#raw_transformation' => 'e_progressbar:width_5',
 *   '#width' => '100%',
 *   '#attributes' => ['class' => ['demo-video']],
 * ];
 * @endcode
 *
 * @RenderElement("cloudinary_video")
 */
class CloudinaryVideo extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#width' => '100%',
      '#autoplay' => FALSE,
      '#raw_transformation' => NULL,
      '#sources' => NULL,
      '#responsive' => TRUE,
      '#resource_type' => 'video',
      '#player_type' => 'html5',
      '#delivery_type' => 'upload',
      '#attributes' => [],
      '#default_transformation' => TRUE,
      '#pre_render' => [
        [get_class($this), 'preRenderVideoElement'],
      ],
    ];
  }

  /**
   * Cloudinary video element pre render callback.
   *
   * @param array $element
   *   An associative array containing the properties of the video element.
   *
   * @return array
   *   The modified element.
   */
  public static function preRenderVideoElement($element) {
    $video_config = \Drupal::config('cloudinary_video.settings');
    $sdk_config = \Drupal::config('cloudinary_sdk.settings');
    $player_type = $element['#player_type'] ?? $video_config->get('cloudinary_default_player');
    $tags = $element['#cache']['tags'] ?? [];
    $element['#cache']['tags'] = Cache::mergeTags($tags, $video_config->getCacheTags());

    switch ($player_type) {
      case 'cloudinary':
        $element['#theme'] = 'video_embed_iframe';
        $element += [
          '#provider' => 'cloudinary',
          '#url' => 'https://player.cloudinary.com/embed/',
          '#query' => [
            'cloud_name' => $sdk_config->get('cloudinary_sdk_cloud_name'),
            'public_id' => $element['#public_id'],
          ],
        ];

        if ($element['#responsive']) {
          $element['#prefix'] = '<div class="video-embed-field-responsive-video">';
          $element['#suffix'] = '</div>';
          $element['#attached']['library'][] = 'video_embed_field/responsive-video';
        }

        $element['#attributes'] = $element['#attributes'] + [
          'width' => $element['#width'],
          'frameborder' => '0',
          'allow' => 'autoplay; fullscreen; encrypted-media; picture-in-picture',
          'allowfullscreen' => 'true',
        ];

        if ($cname = $sdk_config->get('cloudinary_sdk_cname')) {
          $element['#query']['cloudinary'] = [
            'secure_distribution' => $cname,
            'private_cdn' => TRUE,
          ];
        }

        $player = $element['#player'] ?? [];
        if ($player_settings = $video_config->get('cloudinary_video_player_config')) {
          $player += Json::decode($player_settings);
        }

        if ($player) {
          $element['#query']['player'] = $player;

          if ($element['#autoplay']) {
            $element['#query']['player']['autoplay'] = 'true';
            $element['#query']['player']['autoplayMode'] = 'always';
          }

          // Apply default transformation.
          $transformations = [];
          if ($element['#default_transformation']) {
            if ($transformation = $video_config->get('cloudinary_video_optimizations')) {
              $transformations[] = $transformation;
            }
          }

          // Apply custom transformation.
          if ($element['#raw_transformation']) {
            $transformations[] = $element['#raw_transformation'];
          }

          if ($transformations) {
            $element['#query']['source']['transformation']['raw_transformation'] = implode('/', $transformations);
          }
        }
        break;

      case 'html5':
        $video_tag = new VideoTag($element['#public_id'], $element['#sources']);
        $attributes = [
          'width' => $element['#width'],
          'controls' => 'controls',
        ];

        // Set up global player settings.
        if ($player_attributes = $video_config->get('cloudinary_default_player_config')) {
          foreach (explode(' ', $player_attributes) as $attribute) {
            $data = explode('=', $attribute);
            $attributes[$data[0]] = $data[1] ?? $data[0];
          }
        }

        // Override attributes with custom one.
        if (isset($element['#attributes']['class'])) {
          $video_tag->addClass($element['#attributes']['class']);
          unset($element['#attributes']['class']);
        }

        $attributes = $element['#attributes'] + $attributes;

        $video_tag->setAttributes($attributes);
        $video_tag->fallback(t('Your browser does not support HTML5 video tags'));
        $video_tag->deliveryType($element['#delivery_type']);

        // Apply default transformation.
        if ($element['#default_transformation']) {
          if ($transformation = $video_config->get('cloudinary_video_optimizations')) {
            $transformation = new CommonTransformation($transformation);
            $video_tag->addTransformation($transformation);
          }
        }

        // Apply custom transformation.
        if ($element['#raw_transformation']) {
          $transformation = new CommonTransformation($element['#raw_transformation']);
          $video_tag->addTransformation($transformation);
        }

        if ($element['#autoplay']) {
          $video_tag->setAttribute('autoplay', 'autoplay');
        }

        $element['#type'] = 'markup';
        $element += [
          '#markup' => Markup::create((string) $video_tag),
        ];
        break;
    }

    return $element;
  }

}
