<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;


/**
 * Multi-step Appointment Booking Form.
 */
class MultistepForm extends FormBase
{

        /**
         * The entity type manager.
         *
         * @var \Drupal\Core\Entity\EntityTypeManagerInterface
         */
        protected $entityTypeManager;

        /**
         * The renderer service.
         *
         * @var \Drupal\Core\Render\RendererInterface
         */
        protected $renderer;

        /**
         * The messenger service.
         *
         * @var \Drupal\Core\Messenger\MessengerInterface
         */
        protected $messenger;
        protected $mailManager;


        /**
         * Constructs a new MultistepForm object.
         *
         * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
         *   The entity type manager.
         * @param \Drupal\Core\Render\RendererInterface $renderer
         *   The renderer service.
         * @param \Drupal\Core\Messenger\MessengerInterface $messenger
         *   The messenger service.
         * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
         *   The mail manager service.
         */
        public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, MessengerInterface $messenger, MailManagerInterface $mail_manager)
        {
                $this->mailManager = $mail_manager;
                $this->entityTypeManager = $entity_type_manager;
                $this->renderer = $renderer;
                $this->messenger = $messenger;
        }

        /**
         * {@inheritdoc}
         */
        public static function create(ContainerInterface $container)
        {
                return new static(
                        $container->get('entity_type.manager'),
                        $container->get('renderer'),
                        $container->get('messenger'),
                        $container->get('plugin.manager.mail')
                );
        }

        /**
         * {@inheritdoc}
         */
        public function getFormId()
        {
                return 'appointment_multistep_form';
        }

        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form, FormStateInterface $form_state)
        {
                $form['#attached']['library'][] = 'appointment/appointment_form';
                // Initialize step if not set.
                if (!$form_state->has('step')) {
                        $form_state->set('step', 1);
                }
                $step = $form_state->get('step');

                // Progress indicator (now 7 steps).
                $form['progress'] = [
                        '#type' => 'container',
                        '#attributes' => ['class' => ['appointment-progress']],
                        'markup' => [
                                '#markup' => $this->t('Step @current of 7', ['@current' => $step]),
                        ],
                ];

                // Build the current step.
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
                                $form = $this->buildStep5($form, $form_state); // Select Date
                                break;
                        case 6:
                                $form = $this->buildStep6($form, $form_state); // Select Time
                                break;
                        case 7:
                                $form = $this->buildStep7($form, $form_state); // Confirmation
                                break;
                }

                return $form;
        }

        /**
         * Step 1: Personal Details.
         */
        private function buildStep1(array &$form, FormStateInterface $form_state)
        {
                $form['user_name'] = [
                        '#type' => 'textfield',
                        '#title' => $this->t('Your Name'),
                        '#required' => TRUE,
                ];
                $form['user_email'] = [
                        '#type' => 'email',
                        '#title' => $this->t('Your Email'),
                        '#required' => TRUE,
                ];
                $form['user_phone'] = [
                        '#type' => 'tel',
                        '#title' => $this->t('Your Phone Number'),
                        '#required' => FALSE,
                ];
                $form['actions'] = [
                        '#type' => 'actions',
                ];
                $form['actions']['next'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Next'),
                        '#submit' => ['::nextStep'],
                        '#button_type' => 'primary',
                ];
                return $form;
        }

        /**
         * Step 2: Select Agency.
         */
        private function buildStep2(array &$form, FormStateInterface $form_state)
        {
                $form['agency'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Select Agency'),
                        '#options' => $this->getAgencyOptions(),
                        '#required' => TRUE,
                        '#empty_option' => $this->t('- Select an agency -'),
                        '#attributes' => [
                                'class' => ['visually-hidden'], // Hide the original select
                        ],
                ];
                // Create custom agency options markup
                $form['agency_options'] = [
                        '#type' => 'container',
                        '#attributes' => [
                                'class' => ['agency-options'],
                        ],
                ];

                foreach ($this->getAgencyOptions() as $value => $label) {
                        $form['agency_options'][$value] = [
                                '#type' => 'container',
                                '#attributes' => [
                                        'class' => ['agency-option'],
                                        'data-value' => $value,
                                ],
                                'name' => [
                                        '#markup' => '<div class="agency-option-name">' . $label . '</div>',
                                ],
                        ];
                }

                $form['actions'] = [
                        '#type' => 'actions',
                ];
                $form['actions']['prev'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Back'),
                        '#submit' => ['::previousStep'],
                        '#limit_validation_errors' => [],
                ];
                $form['actions']['next'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Next'),
                        '#submit' => ['::nextStep'],
                        '#button_type' => 'primary',
                ];
                $form['#attributes']['class'][] = 'form-item-agency';
                $form['#attached']['library'][] = 'appointment/agency_selection';
                return $form;
        }

        /**
         * Step 3: Select Appointment Type.
         */
        private function buildStep3(array &$form, FormStateInterface $form_state)
        {
                $form['appointment_type'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Select Appointment Type'),
                        '#options' => $this->getAppointmentTypeOptions(),
                        '#required' => TRUE,
                        '#empty_option' => $this->t('- Select a type -'),
                        '#attributes' => [
                                'class' => ['visually-hidden'], // Hide the original select
                        ],
                ];

                // Create custom appointment type options markup
                $form['appointment_type_options'] = [
                        '#type' => 'container',
                        '#attributes' => [
                                'class' => ['appointment-type-options'],
                        ],
                ];
                $appointment_types = $this->getAppointmentTypeOptions();
                foreach ($appointment_types as $value => $label) {
                        $form['appointment_type_options'][$value] = [
                                '#type' => 'container',
                                '#attributes' => [
                                        'class' => [
                                                'appointment-type-option',
                                                $value . '-appointments'
                                        ],
                                        'data-value' => $value,
                                ],
                                'icon' => [
                                        '#markup' => '<div class="appointment-type-icon"></div>',
                                ],
                                'name' => [
                                        '#markup' => '<div class="appointment-type-name">' . $label . '</div>',
                                ],
                        ];
                }
                $form['actions'] = [
                        '#type' => 'actions',
                ];
                $form['actions']['prev'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Back'),
                        '#submit' => ['::previousStep'],
                        '#limit_validation_errors' => [],
                ];
                $form['actions']['next'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Next'),
                        '#submit' => ['::nextStep'],
                        '#button_type' => 'primary',
                ];
                $form['#attached']['library'][] = 'appointment/appointment_type_selection';
                return $form;
        }

        /**
         * Step 4: Select Adviser (filtered by agency and appointment type).
         */
        private function buildStep4(array &$form, FormStateInterface $form_state)
        {
                $agency_id = $form_state->get('agency');
                $appointment_type = $form_state->get('appointment_type');

                $form['adviser'] = [
                        '#type' => 'select',
                        '#title' => $this->t('Select Adviser'),
                        '#options' => $this->getAdviserOptions($agency_id, $appointment_type),
                        '#required' => TRUE,
                        '#empty_option' => $this->t('- Select an adviser -'),
                        '#attributes' => [
                                'class' => ['visually-hidden'], // Hide the original select
                        ],
                ];
                // Create custom adviser options markup
                $form['adviser_options'] = [
                        '#type' => 'container',
                        '#attributes' => [
                                'class' => ['adviser-options'],
                        ],
                ];

                $advisers = $this->getAdviserOptions($agency_id, $appointment_type);
                foreach ($advisers as $value => $label) {
                        $form['adviser_options'][$value] = [
                                '#type' => 'container',
                                '#attributes' => [
                                        'class' => ['adviser-option'],
                                        'data-value' => $value,
                                ],
                                'profile' => [
                                        '#markup' => '
                <div class="adviser-option-profile">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                </div>',
                                ],
                                'details' => [
                                        '#markup' => '
                <div class="adviser-option-details">
                    <div class="adviser-option-name">' . $label . '</div>
                    <div class="adviser-option-role">VOID User</div>
                </div>',
                                ],
                        ];
                }

                $form['actions'] = [
                        '#type' => 'actions'
                ];
                $form['actions']['prev'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Back'),
                        '#submit' => ['::previousStep'],
                        '#limit_validation_errors' => [],
                ];
                $form['actions']['next'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Next'),
                        '#submit' => ['::nextStep'],
                        '#button_type' => 'primary',
                ];
                // Add attached library for JavaScript
                $form['#attached']['library'][] = 'appointment/adviser_selection';
                return $form;
        }

        /**
         * Step 5: Select Date & Time Slot.
         *
         * (No AJAX: available time slots will be recalculated on rebuild.)
         */
        private function buildStep5(array &$form, FormStateInterface $form_state)
        {
                $form['appointment_date'] = [
                        '#type' => 'date',
                        '#title' => $this->t('Select Date'),
                        '#required' => TRUE,
                        '#min' => date('Y-m-d'),
                ];

                $form['actions'] = [
                        '#type' => 'actions',
                ];
                $form['actions']['prev'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Back'),
                        '#submit' => ['::previousStep'],
                        '#limit_validation_errors' => [],
                ];
                $form['actions']['next'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Next'),
                        '#submit' => ['::nextStep'],
                        '#button_type' => 'primary',
                ];

                return $form;
        }

        /**
         * Step 6: Confirmation.
         */
        private function buildStep6(array &$form, FormStateInterface $form_state)
        {
                $adviser_id = $form_state->get('adviser');
                $selected_date = $form_state->get('appointment_date');

                \Drupal::logger('appointment')->notice('BuildStep6: adviser_id: ' . $adviser_id . ', date: ' . $selected_date);

                $form['timeslot_wrapper'] = [
                        '#type' => 'container',
                        '#attributes' => [
                                'id' => 'timeslot-wrapper',
                                'class' => ['timeslot-wrapper'],
                        ],
                ];

                // Get available slots dynamically
                $available_slots = $this->getAvailableTimeSlots($adviser_id, $selected_date);
                \Drupal::logger('appointment')->notice('Available slots count: ' . count($available_slots));

                $form['actions'] = [
                        '#type' => 'actions',
                ];
                $form['actions']['prev'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Back'),
                        '#submit' => ['::previousStep'],
                        '#limit_validation_errors' => [],
                ];
                if (empty($available_slots)) {
                        $form['timeslot_wrapper']['no_slots'] = [
                                '#markup' => $this->t('No available time slots for this date.'),
                        ];
                        $form['actions']['next'] = [
                                '#type' => 'submit',
                                '#value' => $this->t('Next'),
                                '#submit' => ['::nextStep'],
                                '#button_type' => 'primary',
                                '#disabled' => TRUE,
                        ];
                } else {
                        $form['timeslot_wrapper']['appointment_time'] = [
                                '#type' => 'select',
                                '#title' => $this->t('Select Time Slot'),
                                '#options' => $available_slots,
                                '#required' => TRUE,
                                '#empty_option' => $this->t('- Select a time slot -'),
                                '#attributes' => [
                                        'class' => ['visually-hidden'],
                                ],
                        ];
                        // Create custom time slot grid
                        $form['timeslot_wrapper']['timeslot_grid'] = [
                                '#type' => 'container',
                                '#attributes' => [
                                        'class' => ['timeslot-grid'],
                                ],
                        ];
                        foreach ($available_slots as $value => $label) {
                                $form['timeslot_wrapper']['timeslot_grid'][$value] = [
                                        '#type' => 'container',
                                        '#attributes' => [
                                                'class' => ['timeslot-option'],
                                                'data-value' => $value,
                                        ],
                                        '#markup' => $label,
                                ];
                        }
                        $form['actions']['next'] = [
                                '#type' => 'submit',
                                '#value' => $this->t('Next'),
                                '#submit' => ['::nextStep'],
                                '#button_type' => 'primary',
                                '#disabled' => FALSE,
                        ];
                }

                $form['#attached']['library'][] = 'appointment/timeslot_selection';
                return $form;
        }


        /**
         * Step 7: Confirmation.
         */
        private function buildStep7(array &$form, FormStateInterface $form_state)
        {
                $agency_id = $form_state->get('agency');
                $appointment_type = $form_state->get('appointment_type');
                $adviser_id = $form_state->get('adviser');
                $date = $form_state->get('appointment_date');
                $time = $form_state->get('appointment_time');

                $formatted_date = !empty($date) ? date('F j, Y', strtotime($date)) : $this->t('(No date selected)');
                $all_slots = [
                        '09:00' => '09:00 AM',
                        '10:00' => '10:00 AM',
                        '11:00' => '11:00 AM',
                        '14:00' => '02:00 PM',
                        '15:00' => '03:00 PM',
                        '16:00' => '04:00 PM',
                ];
                $display_time = isset($all_slots[$time]) ? $all_slots[$time] : $time;

                $form['summary'] = [
                        '#type' => 'container',
                        '#attributes' => ['class' => ['appointment-summary']],
                ];
                $form['summary']['details'] = [
                        '#type' => 'item',
                        '#title' => $this->t('Review your appointment details:'),
                        '#markup' => '<div class="appointment-details">' .
                                '<p>' . $this->t('Agency: @agency', ['@agency' => $this->getAgencyName($agency_id)]) . '</p>' .
                                '<p>' . $this->t('Appointment Type: @type', ['@type' => $this->getAppointmentTypeName($appointment_type)]) . '</p>' .
                                '<p>' . $this->t('Adviser: @adviser', ['@adviser' => $this->getAdviserName($adviser_id)]) . '</p>' .
                                '<p>' . $this->t('Date: @date', ['@date' => $formatted_date]) . '</p>' .
                                '<p>' . $this->t('Time: @time', ['@time' => $display_time]) . '</p>' .
                                '</div>',
                ];
                $form['actions'] = [
                        '#type' => 'actions',
                ];
                $form['actions']['prev'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Back'),
                        '#submit' => ['::previousStep'],
                        '#limit_validation_errors' => [],
                ];
                $form['actions']['submit'] = [
                        '#type' => 'submit',
                        '#value' => $this->t('Confirm Appointment'),
                        '#button_type' => 'primary',
                ];

                return $form;
        }

        /**
         * Next step handler.
         */
        public function nextStep(array &$form, FormStateInterface $form_state)
        {
                $step = $form_state->get('step');
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

                $form_state->set('step', $step + 1);
                $form_state->setRebuild();
        }

        /**
         * Previous step handler.
         */
        public function previousStep(array &$form, FormStateInterface $form_state)
        {
                $step = $form_state->get('step');
                $form_state->set('step', $step - 1);
                $form_state->setRebuild();
        }

        /**
         * Final submission: Create the appointment entity.
         */
        public function submitForm(array &$form, FormStateInterface $form_state)
        {
                $agency = $form_state->get('agency');
                $appointment_type = $form_state->get('appointment_type');
                $adviser = $form_state->get('adviser');
                $date = $form_state->get('appointment_date');
                $time = $form_state->get('appointment_time');

                // Use values entered by the user in step 1.
                $user_name = $form_state->get('user_name');
                $user_email = $form_state->get('user_email');
                $user_phone = $form_state->get('user_phone');

                // Create the appointment entity with the provided details.
                $appointment = $this->entityTypeManager->getStorage('appointment')->create([
                        'title' => $this->t('Appointment with @adviser on @date at @time', [
                                '@adviser' => $this->getAdviserName($adviser),
                                '@date' => $date,
                                '@time' => $time,
                        ]),
                        'user_id' => \Drupal::currentUser()->id(),
                        'agency' => $agency,
                        'appointment_type' => $appointment_type,
                        'adviser' => $adviser,
                        'appointment_date' => $date,
                        'appointment_time' => $time,
                        'name' => $user_name,
                        'email' => $user_email,
                        'phone' => $user_phone,
                        'status' => 'confirmed',
                ]);
                $appointment->save();
                $appointment_id = $appointment->id();

                $params_user = [
                        'user' => $user_name,
                        'appointment_date' => $date,
                        'appointment_time' => $time,
                        'adviser' => $this->getAdviserName($form_state->get('adviser')),
                        'agency' => $this->getAgencyName($agency),
                ];
                // Get adviser's user email
                $adviser_entity = $this->entityTypeManager->getStorage('adviser')->load($form_state->get('adviser'));
                $adviser_user = $adviser_entity->get('user_id')->entity;
                $adviser_email = $adviser_user->getEmail();

                // Send to user
                $this->mailManager->mail(
                        'appointment',
                        'confirmation_user',
                        $form_state->get('user_email'),
                        \Drupal::languageManager()->getDefaultLanguage()->getId(),
                        $params_user,
                        NULL,
                        TRUE
                );

                $params_adviser = [
                        'user' => $user_name,
                        'appointment_date' => $date,
                        'appointment_time' => $time,
                        'adviser' => $this->getAdviserName($adviser),
                        'agency' => $this->getAgencyName($agency),
                ];
                // Send to adviser
                $this->mailManager->mail(
                        'appointment',
                        'confirmation_adviser',
                        $adviser_email,
                        \Drupal::languageManager()->getDefaultLanguage()->getId(),
                        $params_adviser,
                        NULL,
                        TRUE
                );

                if ($appointment->id()) {
                        $this->messenger->addMessage($this->t('Your appointment has been confirmed!'));
                        $form_state->setRedirect('entity.appointment.canonical', ['appointment' => $appointment->id()]);
                } else {
                        $this->messenger->addMessage($this->t('Error saving the appointment.'), 'error');
                        $form_state->setRebuild();
                }
        }


        /**
         * Returns available agency options.
         */
        private function getAgencyOptions()
        {
                $options = [];
                $agencies = $this->entityTypeManager->getStorage('agency')->loadMultiple();
                foreach ($agencies as $agency) {
                        $options[$agency->id()] = $agency->get('name')->value;
                }
                return $options;
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

        /**
         * Returns the adviser name.
         */
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
         * Returns available time slots for a given adviser and date.
         *
         * This method checks the adviser's working days (stored in a field called
         * "working_days") and removes any slots that are already booked.
         */
        private function getAvailableTimeSlots($adviser_id, $date)
        {
                // 1. Load adviser entity
                $adviser = $this->entityTypeManager->getStorage('adviser')->load($adviser_id);
                \Drupal::logger('appointment')->notice('getAvailableTimeSlots called with adviser_id: ' . $adviser_id . ', date: ' . $date);

                if (!$adviser) {
                        \Drupal::logger('appointment')->error('Adviser not found for ID: ' . $adviser_id);
                        return [];
                }

                // 2. Check if the date is a working day for the adviser
                $selected_day = (date('w', strtotime($date)) + 6) % 7; // 0 = Monday, 1 = Tuesday, ..., 6 = Sunday
                $working_days = array_column($adviser->get('working_hours__day')->getValue(), 'value');
                \Drupal::logger('appointment')->notice('Selected day: ' . $selected_day . ', Adviser working days: ' . json_encode($working_days));


                if (!in_array($selected_day, $working_days)) {
                        \Drupal::logger('appointment')->notice('Adviser unavailable on this day');
                        return [];
                }

                // 3. Get adviser's working hours
                $start_time_str = $adviser->get('working_hours__starthours')->value;
                $end_time_str = $adviser->get('working_hours__endhours')->value;
                \Drupal::logger('appointment')->notice('Adviser working hours: ' . $start_time_str . ' - ' . $end_time_str);


                // 4. Generate all possible time slots (30-minute intervals)
                $slots = $this->generateTimeSlots($date, $start_time_str, $end_time_str, 30);
                \Drupal::logger('appointment')->notice('Generated slots: ' . json_encode(array_keys($slots)));

                // 5. Exclude booked slots
                $booked_times = $this->getBookedTimes($adviser_id, $date);
                \Drupal::logger('appointment')->notice('Booked times: ' . json_encode(array_keys($booked_times)));


                foreach ($booked_times as $time) {
                        unset($slots[$time]);
                }

                return $slots;
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

        private function formatTimeForDisplay($time)
        {
                // Convert "14:00" to "02:00 PM"
                return date('g:i A', strtotime($time));
        }

        private function getBookedTimes($adviser_id, $date)
        {
                $query = $this->entityTypeManager->getStorage('appointment')->getQuery();
                $appointments = $query
                        ->condition('adviser', $adviser_id)
                        ->condition('appointment_date', $date) // Ensure this field matches your entity's date field
                        ->accessCheck(FALSE)
                        ->execute();

                \Drupal::logger('appointment')->notice('Booked appointments count: ' . count($appointments));

                $booked = [];
                foreach ($this->entityTypeManager->getStorage('appointment')->loadMultiple($appointments) as $appointment) {
                        $time = $appointment->get('appointment_time')->value;
                        $booked[$time] = $time;
                }
                return $booked;
        }
}
