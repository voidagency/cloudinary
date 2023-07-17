<?php

namespace Drupal\cloudinary_media_library_widget\Plugin;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class with common functionality for Cloudinary media plugins.
 */
abstract class CloudinaryMediaBase extends PluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Get media source field name.
   *
   * @param string $bundle
   *   The media bundle name.
   *
   * @return string
   *   Field name or nothing.
   */
  protected function getMediaSourceFieldName(string $bundle): string {
    /** @var \Drupal\media\MediaTypeInterface $media_type */
    $media_type = $this->entityTypeManager
      ->getStorage('media_type')
      ->load($bundle);

    if (!$media_type) {
      throw new \Exception(sprintf('The requested media type "%s" not found.', $bundle));
    }

    /** @var \Drupal\media\MediaSourceInterface $source */
    $source = $media_type->getSource();

    return $source->getSourceFieldDefinition($media_type)->getName();
  }

}
