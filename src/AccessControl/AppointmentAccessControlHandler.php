<?php

namespace Drupal\appointment\AccessControl;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeInterface;

class AppointmentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if ($account->hasPermission('view any appointment')) {
                return AccessResult::allowed();
        }
        // Check if user is the owner (using user_id field)
        $owner_id = $entity->get('user_id')->target_id;
        if ($owner_id == $account->id() && $account->hasPermission('view own appointment')) {
                return AccessResult::allowed();
        }
        return AccessResult::forbidden();

      case 'update':
        if ($account->hasPermission('edit any appointment')) {
                return AccessResult::allowed();
        }
        // Check if user is the owner (using user_id field)
        $owner_id = $entity->get('user_id')->target_id;
        if ($owner_id === $account->id() && $account->hasPermission('edit own appointment')) {
                return AccessResult::allowed();
        }
        return AccessResult::forbidden();

      case 'delete':
        if ($account->hasPermission('delete any appointment')) {
                return AccessResult::allowed();
        }
        $owner_id = $entity->get('user_id')->target_id;
        if ($owner_id == $account->id() && $account->hasPermission('delete own appointment')) {
                return AccessResult::allowed();
        }
        return AccessResult::forbidden();
    }
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = null)
  {
      return AccessResult::allowedIfHasPermission($account, 'create appointments');
  }
}