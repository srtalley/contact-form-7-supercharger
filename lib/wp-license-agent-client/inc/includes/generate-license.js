// v1.5
jQuery(function($) {

    $(document).ready(function() {

        var wpla_update_settings_form = $('form.wpla-license-entry.1_5');

        $(wpla_update_settings_form).submit(function(event){
    
            event.preventDefault();

            // get the update slug 
            var wpla_update_slug = $(event.target).data('update-slug');

            // set the hidden ajax value to true to prevent retrieving the license info on submit
            var wpla_license_form_ajax = $(event.target).find('input[name="' + wpla_update_slug + '_wpla_license_form_ajax"]');
            wpla_license_form_ajax.val('true');

            // disable the button 
            var wpla_get_license_button = $(event.target).find('button[name="' + wpla_update_slug + '_wpla_get_license"]');
            wpla_get_license_button.prop("disabled", true);

            // Update form which also checks the license
            var wpla_response_section = $(event.target).find('#' + wpla_update_slug + '_wpla-check-license-response');

            var wpla_disable_functionality = $(event.target).find('#' + wpla_update_slug + '_wpla_disable_functionality');

            var wpla_refresh_on_valid = $(event.target).find('#' + wpla_update_slug + '_wpla_refresh_on_valid');

            var wpla_update_settings_form_data = $(this).serialize();

            var wpla_update_settings_form_action = $(this).attr('action');

            var wpla_update_settings_form_action =
            $.ajax({
                type: 'POST',
                url: wpla_update_settings_form_action,
                data: wpla_update_settings_form_data
            }).done( function(  ) {
                update_wpla_settings_response('<strong>Checking license status...</strong>', wpla_response_section)

            }).then( function () {

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                    action: 'retrieve_license_info-' + wpla_update_slug,
                    get_license_info: true,
                    },
                    success: function( response ) {
                        console.log('Update URL: ' + response.updateserver_url);
                        console.log('Check License URL: ' + response.checklicense_url);
                        var wpla_check_license_response_block = '<strong>License Status:</strong> ' + response.license_data.message;

                        if(response.license_data.expiration != '' && response.license_data.expiration != null) {
                            wpla_check_license_response_block += '&nbsp;<strong>Expiration:</strong> ' + response.license_data.expiration
                        }
                        ;
                        update_wpla_settings_response(wpla_check_license_response_block, wpla_response_section);

                        if( response.license_data.valid ) {
                            $(wpla_response_section).parent().addClass('valid').removeClass('invalid');
                        } else {
                            $(wpla_response_section).parent().addClass('invalid').removeClass('valid');
                        }

                        console.log('Disable functions: ' + $(wpla_disable_functionality).val());
                        console.log('License valid: ' + response.license_data.valid);
                        if(($(wpla_disable_functionality).val() == "true" && response.license_data.valid && response.license_data.disable_functionality) || ($(wpla_refresh_on_valid).val() == "true" && response.license_data.valid)) {
                            var functionality_message = 'License is now valid. Refreshing to enable all functionality...';
                            
                            //make the ajax call to update the transient
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                action: 'set_license_changed_to_valid_transient-' + wpla_update_slug,
                                update_transient: true,
                                },
                            }).done( function() {
                                update_wpla_settings_response(functionality_message, wpla_response_section, true);
                            });
                            
                        } else if ($(wpla_disable_functionality).val() == "false" && !response.license_data.valid && response.license_data.disable_functionality) {
                            var functionality_message = 'License is now invalid. Refreshing to disable functionality...';
                            update_wpla_settings_response(functionality_message, wpla_response_section, true);
                        } else {
                            // enable the button 
                            wpla_get_license_button.prop("disabled", false);
                        }
                    }
                });
            });

        }); // end submit

    }); //end $(document).ready(function()
    
    function update_wpla_settings_response(message = '', target = '', reload = false) {
        $(target).html('<p>' + message + '</p>');

        if(reload) {
            setTimeout(function(){
                location.reload();
            }, 2000);
        } // end if
    } // end function update_wpla_settings_response

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