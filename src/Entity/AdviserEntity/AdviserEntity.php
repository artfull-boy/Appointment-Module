<?php

namespace Drupal\appointment\Entity\AdviserEntity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the Adviser entity.
 *
 * @ContentEntityType(
 *   id = "adviser",
 *   label = @Translation("Adviser"),
 *   label_collection = @Translation("Advisers"),
 *   base_table = "adviser",
 *   data_table = "adviser_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer advisers",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   handlers = {
 * "access" = "Drupal\appointment\AccessControl\AdviserAccessControlHandler",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/adviser/{adviser}",
 *     "add-form" = "/admin/structure/add-adviser",
 *     "edit-form" = "/admin/structure/adviser/{adviser}/edit",
 *     "delete-form" = "/admin/structure/adviser/{adviser}/delete",
 *     "collection" = "/admin/content/advisers",
 *   },
 *   field_ui_base_route = "entity.adviser.collection",
 * )
 */
class AdviserEntity extends ContentEntityBase
{
        use EntityChangedTrait;

        /**
         * {@inheritdoc}
         */
        public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
        {
                $fields = parent::baseFieldDefinitions($entity_type);

                // User reference field
                $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
                        ->setLabel(t('User'))
                        ->setDescription(t('The user associated with this adviser'))
                        ->setSetting('target_type', 'user')
                        ->setRequired(TRUE)
                        ->setDisplayOptions('view', [
                                'label' => 'above',
                                'type' => 'entity_reference_label', // Show user name
                                'weight' => 0,
                            ])
                            ->setDisplayOptions('form', [
                                'type' => 'entity_reference_autocomplete',
                                'weight' => 0,
                                'settings' => [
                                    'match_operator' => 'CONTAINS',
                                    'size' => 60,
                                ],
                            ])
                            ->setDisplayConfigurable('form', TRUE)
                            ->setDisplayConfigurable('view', TRUE);

                // Agency reference field
                $fields['agency'] = BaseFieldDefinition::create('entity_reference')
                        ->setLabel(t('Agency'))
                        ->setDescription(t('The agency the adviser belongs to'))
                        ->setSetting('target_type', 'agency')
                        ->setRequired(TRUE)
                        ->setDisplayOptions('view', [
                                'label' => 'above',
                                'type' => 'entity_reference_label', // Show agency name
                                'weight' => 5,
                            ])
                            ->setDisplayOptions('form', [
                                'type' => 'entity_reference_autocomplete',
                                'weight' => 5,
                                'settings' => [
                                    'match_operator' => 'CONTAINS',
                                    'size' => 60,
                                ],
                            ])
                            ->setDisplayConfigurable('form', TRUE)
                            ->setDisplayConfigurable('view', TRUE);

                // Specializations (Taxonomy reference)
                $fields['specializations'] = BaseFieldDefinition::create('entity_reference')
                        ->setLabel(t('Specializations'))
                        ->setDescription(t('The adviser\'s specializations'))
                        ->setSetting('target_type', 'taxonomy_term')
                        ->setSetting('handler', 'default:taxonomy_term')
                        ->setSetting('handler_settings', [
                                'target_bundles' => ['appointment_type' => 'appointment_type'],
                        ])
                        ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
                        ->setDisplayOptions('view', [
                                'label' => 'above',
                                'type' => 'entity_reference_label', // Show term names
                                'weight' => 10,
                            ])
                            ->setDisplayOptions('form', [
                                'type' => 'entity_reference_autocomplete_tags', // Allow multiple terms
                                'weight' => 10,
                                'settings' => [
                                    'match_operator' => 'CONTAINS',
                                    'size' => 60,
                                ],
                            ])
                            ->setDisplayConfigurable('form', TRUE)
                            ->setDisplayConfigurable('view', TRUE);

                // Working Hours
                $fields['working_hours__day'] = BaseFieldDefinition::create('integer')
                        ->setLabel(t('Working Days'))
                        ->setDescription(t('Days the adviser is available'))
                        ->setSettings([
                                'min' => 0,
                                'max' => 6,
                        ])
                        ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
                        ->setDisplayOptions('view', [
                                'label' => 'above',
                                'type' => 'number_integer', // Display as integer
                                'weight' => 15,
                            ])
                            ->setDisplayOptions('form', [
                                'type' => 'number', // Numeric input for admins
                                'weight' => 15,
                            ])
                            ->setDisplayConfigurable('form', TRUE)
                            ->setDisplayConfigurable('view', TRUE);

                $fields['working_hours__starthours'] = BaseFieldDefinition::create('string')
                        ->setLabel(t('Start Time'))
                        ->setDescription(t('Adviser start working time'))
                        ->setSettings([
                                'max_length' => 5,
                        ])
                        ->setDisplayOptions('view', [
                                'label' => 'above',
                                'type' => 'string',
                                'weight' => 20,
                            ])
                            ->setDisplayOptions('form', [
                                'type' => 'string_textfield',
                                'weight' => 20,
                            ])
                            ->setDisplayConfigurable('form', TRUE)
                            ->setDisplayConfigurable('view', TRUE);

                $fields['working_hours__endhours'] = BaseFieldDefinition::create('string')
                        ->setLabel(t('End Time'))
                        ->setDescription(t('Adviser end working time'))
                        ->setSettings([
                                'max_length' => 5,
                        ])
                        ->setDisplayOptions('view', [
                                'label' => 'above',
                                'type' => 'string',
                                'weight' => 25,
                            ])
                            ->setDisplayOptions('form', [
                                'type' => 'string_textfield',
                                'weight' => 25,
                            ])
                            ->setDisplayConfigurable('form', TRUE)
                            ->setDisplayConfigurable('view', TRUE);

                // Status field
                $fields['status'] = BaseFieldDefinition::create('boolean')
                        ->setLabel(t('Active'))
                        ->setDescription(t('Whether the adviser is currently active'))
                        ->setDefaultValue(TRUE)
                        ->setDisplayOptions('view', [
                                'label' => 'above',
                                'type' => 'boolean', // Show as checkbox in views
                                'settings' => ['display_label' => TRUE],
                                'weight' => 30,
                            ])
                            ->setDisplayOptions('form', [
                                'type' => 'boolean_checkbox',
                                'weight' => 30,
                            ])
                            ->setDisplayConfigurable('form', TRUE)
                            ->setDisplayConfigurable('view', TRUE);
                

                // Created and Changed fields
                $fields['created'] = BaseFieldDefinition::create('created')
                        ->setLabel(t('Created'))
                        ->setDescription(t('The time that the adviser was created'));

                $fields['changed'] = BaseFieldDefinition::create('changed')
                        ->setLabel(t('Changed'))
                        ->setDescription(t('The time that the adviser was last edited'));

                return $fields;
        }

        public function label() {
                $user = $this->get('user_id')->entity;
                return $user ? $user->getAccountName() : $this->t('Unknown Adviser');
              }
}
