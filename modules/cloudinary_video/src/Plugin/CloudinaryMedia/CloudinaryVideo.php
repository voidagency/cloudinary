<?php

namespace Drupal\cloudinary_video\Plugin\CloudinaryMedia;

use Drupal\cloudinary_media_library_widget\Model\Asset;
use Drupal\cloudinary_media_library_widget\Plugin\CloudinaryMediaBase;
use Drupal\cloudinary_media_library_widget\Plugin\CloudinaryMediaPluginInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * Defines a Cloudinary video plugin.
 *
 * @CloudinaryMedia(
 *   id = "cloudinary_video",
 *   label = @Translation("Cloudinary Video"),
 *   resource_type = "video"
 * )
 */
class CloudinaryVideo extends CloudinaryMediaBase implements CloudinaryMediaPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function findMedia(Asset $asset, string $bundle): ?MediaInterface {
    /** @var \Drupal\media\MediaInterface[] $entities */
    $entities = $this->entityTypeManager
      ->getStorage('media')
      ->loadByProperties([
        'name' => $asset->getPublicId(),
        'bundle' => $bundle,
      ]);

    $media = current($entities);

    return ($media instanceof MediaInterface) ? $media : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function createMedia(Asset $asset, string $bundle, FileInterface $file = NULL): MediaInterface {
    if ($media = $this->findMedia($asset, $bundle)) {
      return $media;
    }

    $filepath = explode('://', $asset->getAssetUri($asset))[1];
    $file = File::create([
      'uid' => \Drupal::currentUser()->id(),
      'filename' => basename($filepath),
      'uri' => $asset->getAssetUri($asset),
      'status' => 1,
    ]);
    $file->save();
    $field_name = $this->getMediaSourceFieldName($bundle);
    $media_data = [
      'bundle' => $bundle,
      'name' => $asset->getPublicId(),
      $field_name => $file->id(),
    ];

    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->entityTypeManager->getStorage('media')->create($media_data);
    $media->save();

    return $media;
  }

}
