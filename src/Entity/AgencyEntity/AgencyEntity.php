<?php

namespace Drupal\appointment\Entity\AgencyEntity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the Agency entity.
 *
 * @ContentEntityType(
 *   id = "agency",
 *   label = @Translation("Agency"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\appointment\ListBuilder\AgencyListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\appointment\Form\AgencyForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider"
 *     },
 *     "access" = "Drupal\appointment\AccessControl\AgencyAccessControlHandler",
 *   },
 *   base_table = "agency",
 *   data_table = "agency_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer agencies",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/agency/add",
 *     "canonical" = "/admin/structure/agency/{agency}",
 *     "edit-form" = "/admin/structure/agency/{agency}/edit",
 *     "delete-form" = "/admin/structure/agency/{agency}/delete",
 *     "collection" = "/admin/structure/agency"
 *   }
 * )
 */
class AgencyEntity extends ContentEntityBase
{
    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setRequired(true)
            ->setTranslatable(true)
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
              ]);

        $fields['address'] = BaseFieldDefinition::create('string_long')
            ->setLabel(t('Address'))
            ->setRequired(true)
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string_long',
                'weight' => 0,
              ])
              ->setDisplayOptions('form', [
                'type' => 'text_textarea',
                'weight' => 1,
              ])
              ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'telephone',
                'weight' => 5,
              ])
              ->setDisplayOptions('form', [
                'type' => 'telephone',
                'weight' => 2,
              ]);

        $fields['phone'] = BaseFieldDefinition::create('telephone')
            ->setLabel(t('Phone'))
            ->setRequired(true)
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'number_integer',
                'weight' => 10,
              ]);

        $fields['working_hours__startDay'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Working Hours - Start Day'))
            ->setDescription(t('Day of the week for working hours'))
            ->setSettings([
                'min' => 0,
                'max' => 6, // 0 = Monday, 6 = Sunday
            ])
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'number_integer', // Display as integer
                'weight' => 10,
              ]);

        $fields['working_hours__endDay'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Working Hours - End Day'))
            ->setDescription(t('Day of the week for working hours'))
            ->setSettings([
                'min' => 0,
                'max' => 6, // 0 = Monday, 6 = Sunday
            ])
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'number_integer',
                'weight' => 11,
              ]);

        $fields['working_hours__starthours'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Working Hours - Start Time'))
            ->setDescription(t('Start time for working hours'))
            ->setSettings([
                'max_length' => 5, // Format: HH:MM
                'text_processing' => 0,
            ])
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => 12,
            ]);

        $fields['working_hours__endhours'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Working Hours - End Time'))
            ->setDescription(t('End time for working hours'))
            ->setSettings([
                'max_length' => 5, // Format: HH:MM
                'text_processing' => 0,
            ])
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => 13,
              ]);

        return $fields;
    }
}