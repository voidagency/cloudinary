<?php

namespace Drupal\cloudinary_media_library_widget\Plugin\CloudinaryMedia;

use Drupal\cloudinary_media_library_widget\Model\Asset;
use Drupal\cloudinary_media_library_widget\Plugin\CloudinaryMediaBase;
use Drupal\cloudinary_media_library_widget\Plugin\CloudinaryMediaPluginInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a Cloudinary media file plugin.
 *
 * @CloudinaryMedia(
 *   id = "cloudinary_file",
 *   label = @Translation("Cloudinary File"),
 *   resource_type = "raw"
 * )
 */
class CloudinaryFile extends CloudinaryMediaBase implements CloudinaryMediaPluginInterface {

  /**
   * The MIME type guesser.
   *
   * @var \Symfony\Component\Mime\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->mimeTypeGuesser = $container->get('file.mime_type.guesser');
    $instance->fileSystem = $container->get('file_system');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function findMedia(Asset $asset, string $bundle): ?MediaInterface {
    $properties = [
      'name' => $asset->getPublicId(),
      'bundle' => $bundle,
    ];

    $entities = $this->entityTypeManager
      ->getStorage('media')
      ->loadByProperties($properties);

    return $entities ? end($entities) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function createMedia(Asset $asset, string $bundle): MediaInterface {
    if ($media = $this->findMedia($asset, $bundle)) {
      return $media;
    }

    $file = $this->createFile($asset);
    $field_name = $this->getMediaSourceFieldName($bundle);

    $media_data = [
      'bundle' => $bundle,
      'name' => $asset->getPublicId(),
      $field_name => [
        'target_id' => $file->id(),
      ],
    ];

    if ($custom_context = $asset->getCustomContext()) {
      $media_data[$field_name]['description'] = $custom_context->getAlt();
    }

    /** @var \Drupal\media\MediaInterface $media_entity */
    $media_entity = $this->entityTypeManager->getStorage('media')->create($media_data);
    $media_entity->save();

    return $media_entity;
  }

  /**
   * Create/find a file entity.
   *
   * @param \Drupal\cloudinary_media_library_widget\Model\Asset $asset
   *   Cloudinary asset.
   *
   * @return \Drupal\file\FileInterface
   *   File entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createFile(Asset $asset): FileInterface {
    $uri = $asset->getAssetUri($asset);

    // Check if there is already file with this file uri.
    $file = $this->entityTypeManager
      ->getStorage('file')
      ->loadByProperties([
        'uri' => $uri,
      ]);

    $file = reset($file);

    if ($file instanceof FileInterface) {
      return $file;
    }

    /** @var \Drupal\file\FileInterface $file */
    $file = $this->entityTypeManager->getStorage('file')->create([
      'filename' => $this->fileSystem->basename($uri),
      'uri' => $uri,
      'filemime' => $this->mimeTypeGuesser->guessMimeType($uri),
      'filesize' => $asset->getBytes(),
    ]);
    $file->setPermanent();
    $file->save();

    return $file;
  }

}
