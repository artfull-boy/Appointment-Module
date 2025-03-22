<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AppointmentEditForm extends FormBase
{

        protected $entityTypeManager;
        protected $routeMatch;
        protected $messenger;

        public function __construct(
                EntityTypeManagerInterface $entity_type_manager,
                RouteMatchInterface $route_match,
                MessengerInterface $messenger
        ) {
                $this->entityTypeManager = $entity_type_manager;
                $this->routeMatch = $route_match;
                $this->messenger = $messenger;
        }


        public static function create(ContainerInterface $container)
        {
                return new static(
                        $container->get('entity_type.manager'),
                        $container->get('current_route_match'),
                        $container->get('messenger')
                );
        }

        public function getFormId()
        {
                return 'appointment_edit_form';
        }

        public function buildForm(array $form, FormStateInterface $form_state)
        {

                // Load appointment from route parameter
                $appointment_id = $this->routeMatch->getParameter('appointment');
                $appointment = $this->entityTypeManager->getStorage('appointment')->load($appointment_id);

                $current_user = \Drupal::currentUser();
                if (!$current_user->hasPermission('edit any appointment')) {
                        $owner_id = $appointment->get('user_id')->target_id;
                        if ($owner_id != $current_user->id()) {
                                throw new AccessDeniedHttpException();
                        }
                }
                if (!$appointment) {
                        $this->messenger->addError($this->t('Appointment not found.'));
                        $form_state->setRedirect('<front>');
                        return $form;
                }

                // Initialize step if not set
                if (!$form_state->has('step')) {
                        $form_state->set('step', 1);
                }

                // Store entity in form_state
                $form_state->set('appointment', $appointment);

                $step = $form_state->get('step');

                $form['#attached']['library'][] = 'appointment/appointment_form';

                $form['progress'] = [
                        '#type' => 'container',
                        '#attributes' => ['class' => ['appointment-progress']],
                        'markup' => [
                                '#markup' => $this->t('Step @current of 7', ['@current' => $step]),
                        ],
                ];

                switch ($step) {
                        case 1:
                                $form = $this->buildStep1($form, $form_state);
                                break;
                        case 2:
                                $form = $this->buildStep2($form, $form_state);
                                break;
                        case 3:
                                $form = $this->buildStep3($form, $form_state);
                                break;
                        case 4:
                                $form = $this->buildStep4($form, $form_state);
                                break;
                        case 5:
                                $form = $this->buildStep5($form, $form_state);
                                break;
                        case 6:
                                $form = $this->buildStep6($form, $form_state);
                                break;
                        case 7:
                                $form = $this->buildStep7($form, $form_state);
                                break;
                }

                return $form;
        }

        // Step 1: Personal Details
        protected function buildStep1(array $form, FormStateInterface $form_state)
        {
                $appointment = $form_state->get('appointment');

                $form['user_name'] = [
                        '#type' => 'textfield',
                        '#title' => $this->t('Full Name'),
                        '#default_value' => $appointment->get('name')->value,
                        '#required' => TRUE,
                ];

                $form['user_email'] = [
                        '#type' => 'email',
                        '#title' => $this->t('Email Address'),
                        '#default_value' => $appointment->get('email')->value,
                        '#required' => TRUE,
                ];

                $form['user_phone'] = [
                        '#type' => 'tel',
                        '#title' => $this->t('Phone Number'),
                        '#default_value' => $appointment->get('phone')->value,
                        '#required' => TRUE,
                ];
                $this->addNavigationButtons($form, $form_state, 1);

                return $form;
        }

        // Step 2: Agency Selection
        protected function buildStep2(array $form, FormStateInterface $form_state)
        {
                $appointment = $form_state->get('appointment');

                $form['agency'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Agency'),
                        '#options' => $this->getAgencyOptions(),
                        '#default_value' => $appointment->get('agency')->target_id,
                        '#required' => TRUE,
                        '#empty_option' => $this->t('- Select an agency -'),
                ];

                $this->addNavigationButtons($form, $form_state, 2);

                return $form;
        }

        // Step 3: Appointment Type
        protected function buildStep3(array $form, FormStateInterface $form_state)
        {
                $appointment = $form_state->get('appointment');

                $form['appointment_type'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Appointment Type'),
                        '#options' => $this->getAppointmentTypeOptions(),
                        '#default_value' => $appointment->get('appointment_type')->value,
                        '#required' => TRUE,
                        '#empty_option' => $this->t('- Select a type -'),
                ];

                $this->addNavigationButtons($form, $form_state, 3);

                return $form;
        }

        // Step 4: Adviser Selection
        protected function buildStep4(array $form, FormStateInterface $form_state)
        {
                $appointment = $form_state->get('appointment');
                $agency_id = $form_state->get('agency') ?: $appointment->get('agency')->target_id;
                $type = $form_state->get('appointment_type') ?: $appointment->get('appointment_type')->value;

                $form['adviser'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Select Adviser'),
                        '#options' => $this->getAdviserOptions($agency_id, $type),
                        '#default_value' => $appointment->get('adviser')->target_id,
                        '#required' => TRUE,
                        '#empty_option' => $this->t('- Select an adviser -'),
                ];

                $this->addNavigationButtons($form, $form_state, 4);

                return $form;
        }

        // Step 5: Date Selection
        protected function buildStep5(array $form, FormStateInterface $form_state)
        {
                $appointment = $form_state->get('appointment');

                $form['appointment_date'] = [
                        '#type' => 'date',
                        '#title' => $this->t('Appointment Date'),
                        '#default_value' => $appointment->get('appointment_date')->value,
                        '#required' => TRUE,
                ];

                $this->addNavigationButtons($form, $form_state, 5);

                return $form;
        }

        // Step 6: Time Selection
        protected function buildStep6(array $form, FormStateInterface $form_state)
        {
                $appointment = $form_state->get('appointment');

                $adviser_id = $form_state->get('adviser') ?: $appointment->get('adviser')->target_id;
                $date = $form_state->get('appointment_date') ?: $appointment->get('appointment_date')->value;

                $available_slots = $this->getAvailableTimeSlots($adviser_id, $date, $appointment->id());

                $form['timeslot_wrapper'] = ['#type' => 'container'];

                if (empty($available_slots)) {
                        $form['timeslot_wrapper']['no_slots'] = [
                                '#markup' => $this->t('No available time slots for this date.'),
                        ];
                        $form['actions']['next']['#disabled'] = TRUE;
                } else {
                        $form['timeslot_wrapper']['appointment_time'] = [
                                '#type' => 'select',
                                '#title' => $this->t('Select Time Slot'),
                                '#options' => $available_slots,
                                '#default_value' => $appointment->get('appointment_time')->value,
                                '#required' => TRUE,
                                '#empty_option' => $this->t('- Select a time slot -'),
                        ];
                        $form['actions']['next']['#disabled'] = FALSE;
                }

                $this->addNavigationButtons($form, $form_state, 6);

                return $form;
        }

        // Step 7: Summary
        protected function buildStep7(array $form, FormStateInterface $form_state)
        {
                $appointment = $form_state->get('appointment');
                $agency_id = $form_state->get('agency') ?: $appointment->get('agency')->target_id;
                $type_id = $form_state->get('appointment_type') ?: $appointment->get('appointment_type')->value;
                $adviser_id = $form_state->get('adviser') ?: $appointment->get('adviser')->target_id;
                $date = $form_state->get('appointment_date') ?: $appointment->get('appointment_date')->value;
                $time = $form_state->get('appointment_time') ?: $appointment->get('appointment_time')->value;
                $form['summary'] = [
                        '#type' => 'container',
                        '#attributes' => ['class' => ['appointment-summary']],
                ];

                $form['summary']['details'] = [
                        '#type' => 'item',
                        '#title' => $this->t('Review your appointment details:'),
                        '#markup' => '<div class="appointment-details">'
                                . '<p>' . $this->t('Agency: @agency', ['@agency' => $this->getAgencyName($agency_id)]) . '</p>'
                                . '<p>' . $this->t('Appointment Type: @type', ['@type' => $this->getAppointmentTypeName($type_id)]) . '</p>'
                                . '<p>' . $this->t('Adviser: @adviser', ['@adviser' => $this->getAdviserName($adviser_id)]) . '</p>'
                                . '<p>' . $this->t('Date: @date', ['@date' => date('F j, Y', strtotime($date))]) . '</p>'
                                . '<p>' . $this->t('Time: @time', ['@time' => $this->formatTimeForDisplay($time)]) . '</p>'
                                . '</div>',
                ];

                $this->addNavigationButtons($form, $form_state, 7);

                return $form;
        }

        // Navigation buttons helper
        protected function addNavigationButtons(array &$form, FormStateInterface $form_state, $current_step)
        {
                $form['actions'] = ['#type' => 'actions'];

                if ($current_step > 1) {
                        $form['actions']['prev'] = [
                                '#type' => 'submit',
                                '#value' => $this->t('Back'),
                                '#submit' => ['::previousStep'],
                                '#limit_validation_errors' => [],
                        ];
                }

                if ($current_step < 7) {
                        $form['actions']['next'] = [
                                '#type' => 'submit',
                                '#value' => $this->t('Next'),
                                '#submit' => ['::nextStep'],
                                '#button_type' => 'primary',
                        ];
                } else {
                        $form['actions']['submit'] = [
                                '#type' => 'submit',
                                '#value' => $this->t('Confirm Changes'),
                                '#submit' => ['::submitForm'],
                                '#button_type' => 'primary',
                        ];
                }
        }

        // Step navigation handlers
        public function nextStep(array &$form, FormStateInterface $form_state)
        {
                $step = $form_state->get('step');
                $form_state->set('step', $step + 1);
                $input = $form_state->getUserInput();

                // Persist values based on current step.
                if ($step == 1) {
                        if (isset($input['user_name'])) {
                                $form_state->set('user_name', $input['user_name']);
                        }
                        if (isset($input['user_email'])) {
                                $form_state->set('user_email', $input['user_email']);
                        }
                        if (isset($input['user_phone'])) {
                                $form_state->set('user_phone', $input['user_phone']);
                        }
                }
                if ($step == 2 && isset($input['agency'])) {
                        $form_state->set('agency', $input['agency']);
                }
                if ($step == 3 && isset($input['appointment_type'])) {
                        $form_state->set('appointment_type', $input['appointment_type']);
                }
                if ($step == 4 && isset($input['adviser'])) {
                        $form_state->set('adviser', $input['adviser']);
                }
                if ($step == 5 && isset($input['appointment_date'])) {
                        $form_state->set('appointment_date', $input['appointment_date']);
                }
                if ($step == 6 && isset($input['appointment_time'])) {
                        $form_state->set('appointment_time', $input['appointment_time']);
                }
                $form_state->setRebuild(TRUE);
        }

        public function previousStep(array &$form, FormStateInterface $form_state)
        {
                $step = $form_state->get('step');
                $form_state->set('step', $step - 1);
                $form_state->setRebuild(TRUE);
        }

        // Submit handler
        public function submitForm(array &$form, FormStateInterface $form_state)
        {
                $appointment = $form_state->get('appointment');

                // Get values from form_state or entity
                $user_name = $form_state->get('user_name') ?: $appointment->get('name')->value;
                $user_email = $form_state->get('user_email') ?: $appointment->get('email')->value;
                $user_phone = $form_state->get('user_phone') ?: $appointment->get('phone')->value;
                $agency = $form_state->get('agency') ?: $appointment->get('agency')->target_id;
                $appointment_type = $form_state->get('appointment_type') ?: $appointment->get('appointment_type')->value;
                $adviser = $form_state->get('adviser') ?: $appointment->get('adviser')->target_id;
                $date = $form_state->get('appointment_date') ?: $appointment->get('appointment_date')->value;
                $time = $form_state->get('appointment_time') ?: $appointment->get('appointment_time')->value;

                // Update the existing appointment entity
                $appointment->set('name', $user_name);
                $appointment->set('email', $user_email);
                $appointment->set('phone', $user_phone);
                $appointment->set('agency', $agency);
                $appointment->set('appointment_type', $appointment_type);
                $appointment->set('adviser', $adviser);
                $appointment->set('appointment_date', $date);
                $appointment->set('appointment_time', $time);

                try {
                        $appointment->save();
                        $this->messenger->addMessage($this->t('Your appointment has been updated successfully.'));
                        $form_state->setRedirect('entity.appointment.canonical', ['appointment' => $appointment->id()]);
                } catch (\Exception $e) {
                        $this->messenger->addError($this->t('Error updating appointment: @message', ['@message' => $e->getMessage()]));
                        $form_state->setRebuild(TRUE);
                }
        }

        // Helper methods (same as in MultistepForm)
        private function getAgencyOptions()
        {
                $agencies = $this->entityTypeManager->getStorage('agency')->loadMultiple();
                $options = [];
                foreach ($agencies as $agency) {
                        $options[$agency->id()] = $agency->get('name')->value;
                }
                return $options;
        }

        /**
         * Returns appointment type options from the taxonomy.
         */
        private function getAppointmentTypeOptions()
        {
                $options = [];
                $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => 'appointment_types']);
                foreach ($terms as $term) {
                        $options[$term->id()] = $term->label();
                }
                return $options;
        }

        /**
         * Returns available adviser options based on agency and appointment type.
         */
        private function getAdviserOptions($agency_id, $appointment_type)
        {
                $options = [];
                if (empty($agency_id) || empty($appointment_type)) {
                        return $options;
                }
                // Load advisers for the given agency.
                $advisers = $this->entityTypeManager->getStorage('adviser')->loadByProperties(['agency' => $agency_id]);
                foreach ($advisers as $adviser) {
                        // Check if the adviser offers the selected appointment type using the 'specializations' field.
                        if ($adviser->hasField('specializations')) {
                                $types = $adviser->get('specializations')->getValue();
                                $provided = FALSE;
                                foreach ($types as $type) {
                                        if ($type['target_id'] == $appointment_type) {
                                                $provided = TRUE;
                                                break;
                                        }
                                }
                                if ($provided) {
                                        $user = $adviser->get('user_id')->entity;
                                        if ($user) {
                                                $options[$adviser->id()] = $user->get('name')->value;
                                        }
                                }
                        }
                }
                return $options;
        }

        private function getAvailableTimeSlots($adviser_id, $date, $exclude_id = NULL)
        {
                // Your existing slot logic with exclusion for current appointment
                $adviser = $this->entityTypeManager->getStorage('adviser')->load($adviser_id);

                if (!$adviser) {
                        return [];
                }

                $selected_day = (date('w', strtotime($date)) + 6) % 7;
                $working_days = array_column($adviser->get('working_hours__day')->getValue(), 'value');

                if (!in_array($selected_day, $working_days)) {
                        return [];
                }

                $start_time = $adviser->get('working_hours__starthours')->value;
                $end_time = $adviser->get('working_hours__endhours')->value;

                $slots = $this->generateTimeSlots($date, $start_time, $end_time, 30);
                $booked = $this->getBookedTimes($adviser_id, $date, $exclude_id);

                return array_diff_key($slots, $booked);
        }

        private function getBookedTimes($adviser_id, $date, $exclude_id = NULL)
        {
                $query = $this->entityTypeManager->getStorage('appointment')->getQuery();
                $query->condition('adviser', $adviser_id);
                $query->condition('appointment_date', $date);
                $query->accessCheck(FALSE);
                if ($exclude_id) {
                        $query->condition('id', $exclude_id, '!=');
                }
                $booked = [];
                foreach ($this->entityTypeManager->getStorage('appointment')->loadMultiple($query->execute()) as $app) {
                        $booked_time = $app->get('appointment_time')->value;
                        $booked[$booked_time] = $booked_time;
                }
                return $booked;
        }

        private function formatTimeForDisplay($time)
        {
                // Convert "14:00" to "02:00 PM"
                return date('g:i A', strtotime($time));
        }

        private function getAdviserName($adviser_id)
        {
                if (empty($adviser_id)) {
                        return $this->t('Unknown adviser');
                }
                $adviser = $this->entityTypeManager->getStorage('adviser')->load($adviser_id);
                if ($adviser && !$adviser->get('user_id')->isEmpty()) {
                        $user = $adviser->get('user_id')->entity;
                        return $user ? $user->get('name')->value : $this->t('Unknown adviser');
                }
                return $this->t('Unknown adviser');
        }

        /**
         * Returns the appointment type name.
         */
        private function getAppointmentTypeName($type_id)
        {
                if (empty($type_id)) {
                        return $this->t('Unknown type');
                }
                $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($type_id);
                return $term ? $term->label() : $this->t('Unknown type');
        }

        private function generateTimeSlots($date, $start_time_str, $end_time_str, $interval)
        {
                // Combine date with start/end times
                $start_date_str = "$date $start_time_str";
                $end_date_str = "$date $end_time_str";

                $start = new \DateTime($start_date_str);
                $end = new \DateTime($end_date_str);

                \Drupal::logger('appointment')->notice('Generating slots from: ' . $start->format('Y-m-d H:i') . ' to ' . $end->format('Y-m-d H:i'));

                $slots = [];
                while ($start <= $end) {
                        $slot = $start->format('H:i');
                        $slots[$slot] = $this->formatTimeForDisplay($slot);
                        $start->modify("+$interval minutes");
                }
                return $slots;
        }

        /**
         * Returns the agency name.
         */
        private function getAgencyName($agency_id)
        {
                if (empty($agency_id)) {
                        return $this->t('Unknown agency');
                }
                $agency = $this->entityTypeManager->getStorage('agency')->load($agency_id);
                return $agency ? $agency->get('name')->value : $this->t('Unknown agency');
        }
}
