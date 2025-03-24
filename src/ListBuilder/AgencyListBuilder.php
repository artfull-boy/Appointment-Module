<?php

namespace Drupal\appointment\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;


/**
 * Provides a list controller for the agency entity type.
 */
class AgencyListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    // Add custom columns
    $header['name'] = $this->t('Name');
    $header['address'] = $this->t('Address');
    $header['phone'] = $this->t('Phone');
    $header['working_hours__starthours'] = $this->t('Start Working Hour');
    $header['working_hours__endhours'] = $this->t('End Working Hour');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    /** @var \Drupal\appointment\Entity\AgencyEntity\AgencyEntity $entity */
    $row['name'] = $entity->toLink();
    $row['address'] = $entity->get('address')->value;
    $row['phone'] = $entity->get('phone')->value;
    $row['working_hours__starthours'] = $entity->get('working_hours__starthours')->value;
    $row['working_hours__endhours'] = $entity->get('working_hours__endhours')->value;

    // Add "Edit/Delete" operations (optional)
    $row['operations']['data'] = [
      '#type' => 'operations',
      '#links' => [
        'edit' => [
          'title' => $this->t('Edit'),
          'url' => $entity->toUrl('edit-form'),
        ],
        'delete' => [
          'title' => $this->t('Delete'),
          'url' => $entity->toUrl('delete-form'),
        ],
      ],
    ];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIdColumn() {
    return 'id';
  }
}