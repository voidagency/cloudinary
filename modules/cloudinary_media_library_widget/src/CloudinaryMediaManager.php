<?php

namespace Drupal\cloudinary_media_library_widget;

use Drupal\cloudinary_media_library_widget\Plugin\CloudinaryMediaPluginInterface;
use Drupal\cloudinary_media_library_widget\Plugin\Exception\CloudinaryMediaNotFoundException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\cloudinary_media_library_widget\Annotation\CloudinaryMedia;

/**
 * Cloudinary media plugin manager.
 */
class CloudinaryMediaManager extends DefaultPluginManager implements CloudinaryMediaManagerInterface {

  /**
   * Constructs a CloudinaryMediaManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/CloudinaryMedia',
      $namespaces,
      $module_handler,
      CloudinaryMediaPluginInterface::class,
      CloudinaryMedia::class
    );
    $this->alterInfo('cloudinary_media_info');
    $this->setCacheBackend($cache_backend, 'cloudinary_media_info_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstanceByType(string $type, array $configuration = []): CloudinaryMediaPluginInterface {
    $definitions = $this->getDefinitions();

    foreach ($definitions as $plugin_id => $definition) {
      if ($definition['resource_type'] === $type) {
        return $this->createInstance($plugin_id, $configuration);
      }
    }

    throw new CloudinaryMediaNotFoundException($type);
  }

}
