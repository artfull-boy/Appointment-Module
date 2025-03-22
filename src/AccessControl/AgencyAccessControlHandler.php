<?php

namespace Drupal\appointment\AccessControl;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeInterface;

class AgencyAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view any agency');

      case 'update':
        if ($account->hasPermission('edit any agency')) {
                return AccessResult::allowed();
        }
        // Agency ownership (assuming it has a user_id field)
        $owner_id = $entity->getOwnerId(); // Or $entity->get('user_id')->target_id if structured differently
        if ($owner_id == $account->id() && $account->hasPermission('edit own agency')) {
                return AccessResult::allowed();
        }
        return AccessResult::forbidden();

      case 'delete':
        if ($account->hasPermission('delete any agency')) {
                return AccessResult::allowed();
        }
        $owner_id = $entity->getOwnerId(); // Adjust based on your ownership field
        if ($owner_id == $account->id() && $account->hasPermission('delete own agency')) {
                return AccessResult::allowed();
        }
        return AccessResult::forbidden();

      default:
        return parent::checkAccess($entity, $operation, $account);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = null)
  {
      return AccessResult::allowedIfHasPermission($account, 'create agencies');
  }
}