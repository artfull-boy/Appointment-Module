<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\appointment\Entity\AdviserEntity\AdviserEntity;
use Drupal\Core\Datetime\DrupalDateTime;

class AdviserForm extends FormBase
{

        public function getFormId()
        {
                return 'adviser_form';
        }

        public function buildForm(array $form, FormStateInterface $form_state)
        {
                // User selection
                $form['user_id'] = [
                        '#type' => 'entity_autocomplete',
                        '#target_type' => 'user',
                        '#title' => $this->t('Select User'),
                        '#description' => $this->t('Select the user to be assigned as an adviser'),
                        '#required' => TRUE,
                ];

                // Agency selection
                $form['agency'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Select Agency'),
                        '#target_type' => 'agency',
                        '#required' => TRUE,
                        '#empty_option' => $this->t('- Select an agency -'),
                        '#options' => $this->getAgencyOptions(),
                ];

                // Specializations (Taxonomy terms)
                $form['specializations'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Specializations'),
                        '#multiple' => TRUE,
                        '#options' => $this->getSpecializationOptions(),
                        '#required' => TRUE,
                ];

                // Working Days
                $form['working_days'] = [
                        '#type' => 'checkboxes',
                        '#title' => $this->t('Working Days'),
                        '#options' => [
                                0 => 'Monday',
                                1 => 'Tuesday',
                                2 => 'Wednesday',
                                3 => 'Thursday',
                                4 => 'Friday',
                                5 => 'Saturday',
                                6 => 'Sunday',
                        ],
                        '#required' => TRUE,
                        '#multiple' => TRUE,
                ];

                // Working Hours
                $form['working_hours'] = [
                        '#type' => 'fieldset',
                        '#title' => $this->t('Working Hours'),
                ];

                $form['working_hours']['start_time'] = [
                        '#type' => 'datetime',
                        '#title' => $this->t('Start Time'),
                        '#date_date_element' => 'none',
                        '#date_time_element' => 'time',
                        '#date_increment' => 60,
                        // Set current time without seconds as default value.
                        '#default_value' => DrupalDateTime::createFromFormat('H:i', '09:00'),
                        '#required' => TRUE,
                ];

                $form['working_hours']['end_time'] = [
                        '#type' => 'datetime',
                        '#title' => $this->t('Start Time'),
                        '#date_date_element' => 'none',
                        '#date_time_element' => 'time',
                        '#date_increment' => 60,
                        // Set current time without seconds as default value.
                        '#default_value' => DrupalDateTime::createFromFormat('H:i', '17:00'),
                        '#required' => TRUE,
                ];

                // Status
                $form['status'] = [
                        '#type' => 'checkbox',
                        '#title' => $this->t('Active'),
                        '#default_value' => TRUE,
                ];

                // Submit button
                $form['actions']['submit'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Save Adviser'),
                ];

                return $form;
        }

        public function submitForm(array &$form, FormStateInterface $form_state)
        {
                // Process selected days
                $working_days = $form_state->getValue('working_days');
                
                $selected_days = array_filter($working_days, function ($value) {
                        return $value !== 0;
                      });;
            
                $start_time_full = $form_state->getValue('start_time');
                $end_time_full = $form_state->getValue('end_time');

                // Create DateTime object and format to HH:MM
                $start_time = (new \DateTime($start_time_full))->format('H:i');
                $end_time = (new \DateTime($end_time_full))->format('H:i');
                // Create adviser entity
                $adviser = AdviserEntity::create([
                        'user_id' => $form_state->getValue('user_id'),
                        'agency' => $form_state->getValue('agency'),
                        'specializations' => $form_state->getValue('specializations'),
                        'working_hours__day' => array_keys($selected_days), // First selected day
                        'working_hours__starthours' => $start_time,
                        'working_hours__endhours' => $end_time,
                        'status' => $form_state->getValue('status'),
                ]);

                // Save the entity
                $adviser->save();

                // Show success message
                $this->messenger()->addMessage($this->t('Adviser created successfully.'));
        }

        // Helper method to get agency options
        protected function getAgencyOptions()
        {
                $agencies = \Drupal::entityTypeManager()
                        ->getStorage('agency')
                        ->loadMultiple();

                $options = [];
                foreach ($agencies as $agency) {
                        $options[$agency->id()] = $agency->get('name')->value;
                }

                return $options;
        }

        // Helper method to get specialization options
        protected function getSpecializationOptions()
        {
                $appointment_types = \Drupal::entityTypeManager()
                        ->getStorage('taxonomy_term')
                        ->loadByProperties([
                                'vid' => 'appointment_types', // The machine name of your vocabulary
                        ]);

                $options = [];
                foreach ($appointment_types as $type) {
                        $options[$type->id()] = $type->getName(); // Use getName() instead of get('name')->value
                }

                return $options;
        }
}
