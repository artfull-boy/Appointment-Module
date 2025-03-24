<?php

namespace Drupal\appointment\Entity\AppointmentEntity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Appointment entity.
 *
 * @ContentEntityType(
 *   id = "appointment",
 *   label = @Translation("Appointment"),
 *   label_collection = @Translation("Appointments"),
 *   base_table = "appointment",
 *   data_table = "appointment_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer appointments",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   handlers = {
 * "access" = "Drupal\appointment\AccessControl\AppointmentAccessControlHandler",
 *     "list_builder" = "Drupal\appointment\ListBuilder\AppointmentListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\appointment\Form\AppointmentEditForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/appointment/{appointment}",
 *     "add-form" = "/appointment/add",
 *     "edit-form" = "/appointment/{appointment}/edit",
 *     "delete-form" = "/appointment/{appointment}/delete",
 *     "collection" = "/admin/content/appointments",
 *   },
 *   field_ui_base_route = "entity.appointment.collection",
 * )
 */
class AppointmentEntity extends ContentEntityBase
{
    use EntityOwnerTrait;
    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        // Core fields.
        $fields['title'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Title'))
            ->setRequired(TRUE)
            ->setTranslatable(TRUE)
            ->setSettings([
                'max_length' => 255,
                'text_processing' => 0,
            ])
            ->setDisplayOptions('view', [
                'label' => 'hidden',
                'type' => 'string',
                'weight' => -5,
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => -5,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('User ID'))
            ->setSetting('target_type', 'user')
            ->setDefaultValue(1)
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'author',
                'weight' => 0,
            ])
            ->setDisplayOptions('form', [
                'type' => 'entity_reference_autocomplete',
                'weight' => 5,
                'settings' => [
                    'match_operator' => 'CONTAINS',
                    'size' => '60',
                    'autocomplete_type' => 'tags',
                    'placeholder' => '',
                ],
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['status'] = BaseFieldDefinition::create('list_string')
            ->setLabel(t('Status'))
            ->setRequired(true)
            ->setSettings([
                'allowed_values' => [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'cancelled' => 'Cancelled',
                ],
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setTranslatable(TRUE);

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setTranslatable(TRUE);

        // Additional fields.
        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setTranslatable(TRUE);

        $fields['email'] = BaseFieldDefinition::create('email')
            ->setLabel(t('Email'))
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'email_mailto',
                'weight' => 40,
            ])
            ->setDisplayOptions('form', [
                'type' => 'email_default',
                'weight' => 40,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['phone'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Phone'))
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string',
                'weight' => 45,
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => 45,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['agency'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Agency'))
            ->setSetting('target_type', 'agency')
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label',
                'weight' => 10,
            ])
            ->setDisplayOptions('form', [
                'type' => 'entity_reference_autocomplete',
                'weight' => 10,
                'settings' => [
                    'match_operator' => 'CONTAINS',
                    'size' => '60',
                    'placeholder' => '',
                ],
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['appointment_type'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Appointment Type'))
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string',
                'weight' => 15,
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => 15,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['adviser'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Adviser'))
            ->setSetting('target_type', 'adviser')
            ->setTranslatable(TRUE)
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['appointment_date'] = BaseFieldDefinition::create('datetime')
            ->setLabel(t('Appointment Date'))
            ->setTranslatable(TRUE)
            ->setSettings([
                'datetime_type' => 'date',
            ])
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'datetime_default',
                'weight' => 25,
            ])
            ->setDisplayOptions('form', [
                'type' => 'datetime_default',
                'weight' => 25,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['appointment_time'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Appointment Time'))
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'string',
                'weight' => 30,
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => 30,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);



        return $fields;
    }
}