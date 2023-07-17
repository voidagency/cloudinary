<?php

namespace Drupal\cloudinary_media_library_widget\Access;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Checks access for cloudinary media.
 */
class CloudinaryMediaAccess implements AccessInterface {

  /**
   * Checks access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param string $bundle
   *   Media type.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access.
   */
  public function access(AccountInterface $account, string $bundle): AccessResultInterface {
    return AccessResult::allowedIf(
      $account->hasPermission('create ' . $bundle . ' media')
    );
  }

}
