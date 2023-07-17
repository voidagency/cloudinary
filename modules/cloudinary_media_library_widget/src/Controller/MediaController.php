<?php

namespace Drupal\cloudinary_media_library_widget\Controller;

use Drupal\cloudinary_media_library_widget\Model\Asset;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Media controller.
 */
class MediaController extends ControllerBase {

  /**
   * The serializer which serializes the views result.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * The Cloudinary media manager.
   *
   * @var \Drupal\cloudinary_media_library_widget\CloudinaryMediaManagerInterface
   */
  protected $cloudinaryMediaManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->serializer = $container->get('serializer');
    $instance->cloudinaryMediaManager = $container->get('cloudinary_media_library_widget.manager.cloudinary_media');

    return $instance;
  }

  /**
   * Returns Ajax Response containing the media id and preview.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   * @param string $bundle
   *   Media bundle type.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   *
   * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
   */
  public function createMedia(Request $request, string $bundle): JsonResponse {
    $assets_data = Json::decode($request->getContent());

    if (is_null($assets_data)) {
      throw new \InvalidArgumentException('Assert data have not been provided');
    }

    $ids = [];

    foreach ($assets_data as $asset_data) {
      /** @var \Drupal\cloudinary_media_library_widget\Model\Asset $asset */
      $asset = $this->serializer->denormalize($asset_data, Asset::class);

      $cloudinary_media_plugin = $this->cloudinaryMediaManager->createInstanceByType($asset->getResourceType());
      $media = $cloudinary_media_plugin->createMedia($asset, $bundle);
      $ids[] = $media->id();
    }

    return new JsonResponse([
      'ids' => $ids,
    ]);
  }

}
