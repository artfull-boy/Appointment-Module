<?php

namespace Drupal\appointment\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Link;


/**
 * Provides a list controller for the agency entity type.
 */
class AppointmentListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    // Add custom columns
    $header['title'] = $this->t('Title');
    $header['name'] = $this->t('Customer Name');
    $header['agency'] = $this->t('Agency');
    $header['adviser'] = $this->t('Adviser');
    $header['appointment_type'] = $this->t('Specialization');
    $header['appointment_date'] = $this->t('Appointment Date');
    $header['appointment_time'] = $this->t('Appointment Time');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    /** @var \Drupal\appointment\Entity\AppointmentEntity\AppointmentEntity $entity */
    $row['title'] = $entity->toLink();
    $row['name'] = $entity->get('name')->value;
    $row['agency'] = \Drupal::entityTypeManager()->getStorage('agency')->load($entity->get('agency')->target_id)->label();
    $adviser_id = $entity->get('adviser')->target_id ?: NULL;
    $row['adviser'] = $adviser_id ? $this->getAdviserName($adviser_id) : $this->t('N/A');
    $term_id = $entity->get('appointment_type')->value ?: NULL;
    $row['appointment_type'] = $term_id ? $this->getAppointmentTypeName($term_id) : $this->t('N/A');
    $row['appointment_date'] = $entity->get('appointment_date')->value;
    $row['appointment_time'] = $entity->get('appointment_time')->value;

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

  /**
   * Returns the adviser's user name.
   */
  private function getAdviserName($adviser_id) {
    if (empty($adviser_id)) {
      return $this->t('Unknown adviser');
    }
    $adviser = \Drupal::entityTypeManager()->getStorage('adviser')->load($adviser_id);
    if ($adviser && $adviser->get('user_id')->entity) {
      return $adviser->get('user_id')->entity->getAccountName();
    }
    return $this->t('N/A');
  }

  /**
   * Returns the appointment type term name.
   */
  private function getAppointmentTypeName($term_id) {
    if (empty($term_id)) {
      return $this->t('Unknown type');
    }
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => 'appointment_types','tid'=>$term_id]);
    $term = reset($terms);
    return $term ? $term->label() : $this->t('N/A');
  }
}