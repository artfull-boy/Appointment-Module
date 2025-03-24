<?php

namespace Drupal\appointment\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Render\Markup;

/**
 * Provides a list controller for the agency entity type.
 */
class AdviserListBuilder extends EntityListBuilder
{

  /**
   * {@inheritdoc}
   */
  public function buildHeader()
  {

    // Add custom columns
    $header['user_id'] = $this->t('Name');
    $header['agency'] = $this->t('Agency');
    $header['specializations'] = $this->t('Specializations');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity)
  {

    /** @var \Drupal\appointment\Entity\AdviserEntity\AdviserEntity $entity */
    $row['user_id'] = $entity->get('user_id')->entity->toLink();
    $row['agency'] = $entity->get('agency')->entity->toLink();
    $specializations = $entity->get('specializations')->referencedEntities();
    $terms = array_map(function ($term) {
      return $term->toLink()->toString();
    }, $specializations);
    $row['specializations'] = Markup::create(implode(', ', $terms)) ?: $this->t('None');

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
  protected function getEntityIdColumn()
  {
    return 'id';
  }
}
