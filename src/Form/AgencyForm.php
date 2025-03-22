<?php

namespace Drupal\appointment\Form;

use Drupal\appointment\Entity\AgencyEntity\AgencyEntity;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Element\Datetime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AgencyForm extends FormBase
{
        // Days of the week
        private $days = [
                0 => 'Monday',
                1 => 'Tuesday',
                2 => 'Wednesday',
                3 => 'Thursday',
                4 => 'Friday',
                5 => 'Saturday',
                6 => 'Sunday',
        ];

        /**
         * {@inheritdoc}
         */
        public function getFormId()
        {
                return 'agency_form';
        }

        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form, FormStateInterface $form_state)
        {

                $form['name'] = [
                        '#type' => 'textfield',
                        '#title' => $this->t('Agency Name'),
                        '#required' => TRUE,
                ];

                $form['address'] = [
                        '#type' => 'textfield',
                        '#title' => $this->t('Agency Address'),
                        '#required' => TRUE,
                ];

                $form['phone'] = [
                        '#type' => 'tel',
                        '#title' => $this->t('Agency Phone Number'),
                        '#required' => TRUE,
                ];

                // Working Days
                $form['working_days'] = [
                        '#type' => 'fieldset',
                        '#title' => $this->t('Working Days'),
                ];

                $form['working_days']['start_day'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Start Day'),
                        '#options' => $this->days,
                        '#required' => TRUE,
                ];

                $form['working_days']['end_day'] = [
                        '#type' => 'select',
                        '#title' => $this->t('End Day'),
                        '#options' => $this->days,
                        '#required' => TRUE,
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
                        '#title' => $this->t('End Time'),
                        '#date_date_element' => 'none',
                        '#date_time_element' => 'time',
                        '#date_increment' => 60,

                        // Set current time without seconds as default value.
                        '#default_value' => DrupalDateTime::createFromFormat('H:i', '17:00'),
                        '#required' => TRUE,
                ];

                // Submit button
                $form['actions']['submit'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Create Agency'),
                ];

                return $form;
        }

        /**
         * {@inheritdoc}
         */
        public function submitForm(array &$form, FormStateInterface $form_state)
        {
                // Validate that end day is not before start day
                $start_day = $form_state->getValue('start_day');
                $end_day = $form_state->getValue('end_day');
                if ($end_day < $start_day) {
                        $form_state->setError($form['working_days']['end_day'], $this->t('End day must be after or equal to start day.'));
                        return;
                }
                // Convert full datetime to time
                $start_time_full = $form_state->getValue( 'start_time');
                $end_time_full = $form_state->getValue( 'end_time');

                // Create DateTime object and format to HH:MM
                $start_time = (new \DateTime($start_time_full))->format('H:i');
                $end_time = (new \DateTime($end_time_full))->format('H:i');

                $agency = AgencyEntity::create([
                        'name' => $form_state->getValue('name'),
                        'address' => $form_state->getValue('address'),
                        'phone' => $form_state->getValue('phone'),
                        'working_hours__startDay' => $start_day,
                        'working_hours__endDay' => $end_day,
                        'working_hours__starthours' => $start_time,
                        'working_hours__endhours' => $end_time,
                ]);

                // Save the entity.
                $agency->save();

                // Display a confirmation message.
                $this->messenger()->addMessage($this->t('Agency added successfully.'));
        }

        /**
         * Form validation handler.
         */
        public function validateForm(array &$form, FormStateInterface $form_state)
        {
                // Additional validation can be added here
                parent::validateForm($form, $form_state);
        }
}
