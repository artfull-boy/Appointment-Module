(function ($, Drupal) {
        Drupal.behaviors.timeslotSelection = {
            attach: function (context, settings) {
                $('.timeslot-grid .timeslot-option', context)
                    .not('.timeslot-processed')
                    .addClass('timeslot-processed')
                    .on('click', function() {
                        // Remove selected class from all options
                        $('.timeslot-grid .timeslot-option').removeClass('selected');
                        
                        // Add selected class to clicked option
                        $(this).addClass('selected');
                        
                        // Update the hidden select element
                        const value = $(this).data('value');
                        $('select[name="appointment_time"]').val(value);
                    });
            }
        };
    })(jQuery, Drupal);