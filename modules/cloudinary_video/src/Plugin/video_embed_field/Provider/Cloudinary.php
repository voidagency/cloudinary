<?php

namespace Drupal\cloudinary_video\Plugin\video_embed_field\Provider;

use Cloudinary\Asset\Media;
use Drupal\Core\File\FileSystemInterface;
use Drupal\video_embed_field\ProviderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Cloudinary provider plugin.
 *
 * @VideoEmbedProvider(
 *   id = "cloudinary",
 *   title = @Translation("Cloudinary")
 * )
 */
class Cloudinary extends ProviderPluginBase {

  /**
   * The config factory interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->configFactory = $container->get('config.factory');

    return $instance;
  }

  /**
   * Get the video format.
   *
   * @return string
   *   The video format.
   */
  protected function getVideoFormat() {
    $info = pathinfo($this->getVideoId());

    return $info['extension'];
  }

  /**
   * Get the asset public id.
   *
   * @return string
   *   The asset public id.
   */
  protected function getAssetPublicId() {
    $info = pathinfo($this->getVideoId());

    if ($info['dirname'] === '.') {
      return $info['filename'];
    }

    return $info['dirname'] . '/' . $info['filename'];
  }

  /**
   * Get default image transformations.
   */
  protected function getImageTransformations() {
    return $this->configFactory
      ->get('cloudinary_media_library_widget.settings')
      ->get('cloudinary_image_optimizations');
  }

  /**
   * {@inheritdoc}
   */
  public function downloadThumbnail() {
    $local_uri = $this->getLocalThumbnailUri();
    if (!file_exists($local_uri)) {
      $directory = $this->thumbsDirectory;
      $info = pathinfo($this->getVideoId());

      if ($info['dirname'] !== '.') {
        $directory .= "/{$info['dirname']}";
      }

      $this->getFileSystem()->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      try {
        $thumbnail = $this->httpClient->request('GET', $this->getRemoteThumbnailUrl());
        $this->getFileSystem()->saveData((string) $thumbnail->getBody(), $local_uri);
      }
      catch (\Exception $e) {
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    $source = str_replace(".{$this->getVideoFormat()}", '.jpg', $this->getVideoId());
    $options['secure'] = TRUE;
    $options['resource_type'] = 'video';

    if ($transformation = $this->getImageTransformations()) {
      $options['raw_transformation'] = $transformation;
    }

    return Media::fromParams($source, $options)->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay) {
    return [
      '#type' => 'cloudinary_video',
      '#public_id' => $this->getAssetPublicId(),
      '#width' => $width,
      '#autoplay' => $autoplay,
      // Responsive video is hadned on formatter level.
      '#responsive' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    // Get public id from the video schema.
    if (str_contains($input, 'cloudinary://')) {
      return substr($input, strlen('cloudinary://'));
    }

    if (str_contains($input, '/video/upload/')) {
      \Drupal::messenger()->addWarning(t('External cloudinary URL is currently not supported.'));
    }

    return FALSE;
  }

}
