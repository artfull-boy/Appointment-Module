(function ($, Drupal) {
        Drupal.behaviors.adviserSelection = {
            attach: function (context, settings) {
                $('.adviser-options .adviser-option', context)
                    .not('.adviser-select-processed')
                    .addClass('adviser-select-processed')
                    .on('click', function() {
                        // Remove selected class from all options
                        $('.adviser-options .adviser-option').removeClass('selected');
                        
                        // Add selected class to clicked option
                        $(this).addClass('selected');
                        
                        // Update the hidden select element
                        const value = $(this).data('value');
                        $('select[name="adviser"]').val(value);
                    });
            }
        };
    })(jQuery, Drupal);