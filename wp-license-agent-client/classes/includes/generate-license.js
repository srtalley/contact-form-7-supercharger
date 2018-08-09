jQuery(function($) {

    $(document).ready(function() {

        var wpla_update_settings_form = $('form.wpla-license-entry');

        $(wpla_update_settings_form).submit(function(event){
    
            event.preventDefault();
            
            // get the update slug 
            var wpla_update_slug = $(event.target).data('update-slug');

            // Update form which also checks the license
            var wpla_update_settings_response = $(event.target).find('#' + wpla_update_slug + '_wpla-check-license-response');

            var wpla_disable_functionality = $(event.target).find('#' + wpla_update_slug + '_wpla_disable_functionality');

            var wpla_update_settings_form_data = $(this).serialize();

            var wpla_update_settings_form_action = $(this).attr('action');

            var wpla_update_settings_form_action =
            $.ajax({
                type: 'POST',
                url: wpla_update_settings_form_action,
                data: wpla_update_settings_form_data
            }).done( function(  ) {
                
                // var current_time = new Date($.now());

                $(wpla_update_settings_response).html('<p><strong>Checking license status...</strong></p>');
            }).then( function () {

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                    action: 'retrieve_news-' + wpla_update_slug,
                    get_news: true,
                    },
                    success: function( response ) {
                        console.log('Update URL: ' + response.update_url);
                        console.log('Check License URL: ' + response.checklicense_url);
                        var wpla_check_license_response_block = '<p><strong>License Status:</strong> ' + response.news_data.message;

                        if(response.news_data.expiration != '' && response.news_data.expiration != null) {
                            wpla_check_license_response_block += '&nbsp;<strong>Expiration:</strong> ' + response.news_data.expiration
                        }
                        wpla_check_license_response_block += '</p>';
                        $(wpla_update_settings_response).html(wpla_check_license_response_block);
                        if( response.news_data.valid ) {
                            $(wpla_update_settings_response).parent().addClass('valid').removeClass('invalid');
                        } else {
                            $(wpla_update_settings_response).parent().addClass('invalid').removeClass('valid');
                        }
                        console.log(response.news_data.disable_functionality);
                        console.log('Disable functions: ' + $(wpla_disable_functionality).val());
                        console.log('License valid: ' + response.news_data.valid);
                        if($(wpla_disable_functionality).val() == "true" && response.news_data.valid && response.news_data.disable_functionality) {
                            var functionality_message = 'License is now valid. Refreshing to enable all functionality.';
                            console.log(functionality_message);
                            $(wpla_update_settings_response).html('<p>' + functionality_message + '</p>');
                            location.reload();
                        } else if ($(wpla_disable_functionality).val() == "false" && !response.news_data.valid && response.news_data.disable_functionality) {
                            var functionality_message = 'License is now invalid. Refreshing to disable functionality.';
                            console.log(functionality_message);
                            $(wpla_update_settings_response).html('<p>' + functionality_message + '</p>');
                            location.reload();
                        }
                    }
                });
            });

        }); // end $(wc_test_form).submit(function(event)

    }); //end $(document).ready(function()
    
    $('.wpla-update-license-lightbox').click( function(event) {
        event.preventDefault();
        var wpla_update_slug = $( event.target ).data( 'update-slug' );
        //Show the modal
        showDSModal('#' + wpla_update_slug + '-wpla-lightbox-modal');
    });

    //Remove model on close or clicking outside the box
    $('body').on('click', '.wpla-lightbox-modal-background, .wpla-lightbox-close',function(){
        var wpla_lightbox_open = $(this).closest('.wpla-lightbox-modal');
        hideDSModal(wpla_lightbox_open);
    });

    function showDSModal(modal_object){
        $('body').find(modal_object).addClass('wpla-lightbox-modal-show-bg wpla-lightbox-modal-show-message');
    } // end function showDSModal

    function hideDSModal(modal_object){
        $(modal_object).removeClass('wpla-lightbox-modal-show-bg wpla-lightbox-modal-show-message');
    } // end function hideDSModal
});