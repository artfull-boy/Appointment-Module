(function ($, Drupal) {
        Drupal.behaviors.agencySelection = {
            attach: function (context, settings) {
                $('.agency-options .agency-option', context)
                    .not('.agency-select-processed')
                    .addClass('agency-select-processed')
                    .on('click', function() {
                        // Remove selected class from all options
                        $('.agency-options .agency-option').removeClass('selected');
                        
                        // Add selected class to clicked option
                        $(this).addClass('selected');
                        
                        // Update the hidden select element
                        const value = $(this).data('value');
                        $('select[name="agency"]').val(value);
                    });
            }
        };
    })(jQuery, Drupal);