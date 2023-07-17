<?php

namespace Drupal\cloudinary_media_library_widget\Normalizer;

use Drupal\cloudinary_media_library_widget\Model\Asset;
use Drupal\cloudinary_media_library_widget\Model\Context\Custom;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

/**
 * Asset normalizer.
 */
class AssetNormalizer extends PropertyNormalizer {

  /**
   * Class name which is supported by the normalizer.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = Asset::class;

  /**
   * {@inheritdoc}
   */
  protected $ignoredAttributes = ['context'];

  /**
   * {@inheritdoc}
   */
  public function supportsDenormalization($data, $type, $format = NULL) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    throw new \LogicException('This method should never be called.');
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $type, $format = NULL, array $context = []) {
    /** @var \Drupal\cloudinary_media_library_widget\Model\Asset $cloudinary_asset */
    $cloudinary_asset = parent::denormalize($data, $type, $format, $context);

    if (isset($data['context']) && isset($data['context']['custom'])) {
      /** @var \Drupal\cloudinary_media_library_widget\Model\Context\Custom $custom */
      $custom = $this->denormalize($data['context']['custom'], Custom::class, $format, $context);

      $cloudinary_asset->setDenormalizedCustom($custom);
    }

    return $cloudinary_asset;
  }

}

