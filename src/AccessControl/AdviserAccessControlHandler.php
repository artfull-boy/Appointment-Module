<?php

namespace Drupal\appointment\AccessControl;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeInterface;

class AdviserAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account,'view any adviser');

      case 'update':
        if ($account->hasPermission('edit any adviser')) {
                return AccessResult::allowed();
        }
        // Adviser is owned by the referenced user (user_id field)
        $owner_id = $entity->get('user_id')->target_id;
        if ($owner_id == $account->id() && $account->hasPermission('edit own adviser')) {
                return AccessResult::allowed();
        }
        return AccessResult::forbidden();

      case 'delete':
        if ($account->hasPermission('delete any adviser')) {
                return AccessResult::allowed();
        }
        $owner_id = $entity->get('user_id')->target_id;
        if ($owner_id == $account->id() && $account->hasPermission('delete own adviser')) {
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
        return AccessResult::allowedIfHasPermission($account, 'create advisers');
    }
}