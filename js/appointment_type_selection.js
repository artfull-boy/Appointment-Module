(function ($, Drupal) {
        Drupal.behaviors.appointmentTypeSelection = {
            attach: function (context, settings) {
                $('.appointment-type-options .appointment-type-option', context)
                    .not('.appointment-type-processed')
                    .addClass('appointment-type-processed')
                    .on('click', function() {
                        // Remove selected class from all options
                        $('.appointment-type-options .appointment-type-option').removeClass('selected');
                        
                        // Add selected class to clicked option
                        $(this).addClass('selected');
                        
                        // Update the hidden select element
                        const value = $(this).data('value');
                        $('select[name="appointment_type"]').val(value);
                    });
            }
        };
    })(jQuery, Drupal);