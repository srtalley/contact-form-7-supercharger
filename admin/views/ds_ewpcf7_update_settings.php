<?php $wpla_update_settings_plugin_settings = $this->plugin_settings;
$wpla_update_settings_plugin_settings_slug = $wpla_update_settings_plugin_settings['page_slug'];
?>
<script>
jQuery(function($) {

  $(document).ready(function() {

    // Update form which also checks the license
    var wpla_check_license_button = $('#wpla_check_license');

    var wpla_check_license_response = $('#wpla_check_license-response');

    var wpla_update_settings_response = $('#wpla_check_license-response');

    var wpla_update_settings_form = $("form[id$='update_settings']");

    var wpla_disable_functionality = $('#wpla_disable_functionality');

    $(wpla_update_settings_form).submit(function(event){
      event.preventDefault();

      var wpla_update_settings_form_data = $(wpla_update_settings_form).serialize();

      var wpla_update_settings_form_action = $(wpla_update_settings_form).attr('action');

      var wpla_update_settings_response_data = '';
      var wpla_update_settings_form_action =
        $.ajax({
          type: 'POST',
          url: wpla_update_settings_form_action,
          data: wpla_update_settings_form_data
        }).done( function( data ) {
          var current_time = new Date($.now());

          $(wpla_update_settings_response).html('<p><strong>Checking license status...</strong></p>');
        }).then( function (data) {
          $.ajax({
              url: ajaxurl,
              type: 'POST',
              data: {
                action: 'retrieve_news-<?php echo $wpla_update_settings_plugin_settings_slug; ?>',
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
                $(wpla_check_license_response).html(wpla_check_license_response_block);
                console.log('Disable functions: ' + $(wpla_disable_functionality).val());
                console.log('License valid: ' + response.news_data.valid);
                if($(wpla_disable_functionality).val() == "true" && response.news_data.valid && response.news_data.disable_functionality) {
                  console.log('License is now valid. Refreshing to enable all functionality.');
                  location.reload();
                } else if ($(wpla_disable_functionality).val() == "false" && !response.news_data.valid && response.news_data.disable_functionality) {
                  console.log('License is now invalid. Refreshing to disable functionality.');
                  location.reload();
                }
              }
          });
        });

    }); // end $(wc_test_form).submit(function(event)

  }); //end $(document).ready(function()

});
</script>

<?php 

//get license info
$license_info = get_option($wpla_update_settings_plugin_settings['page_slug'] . '_daily_news_check', true);

$license_message = isset($license_info->message) ? $license_info->message : 'Unknown';
$license_expiration = isset($license_info->expiration) ? $license_info->expiration : 'Unknown';
$license_valid = isset($license_info->valid) ? $license_info->valid : false;
$disable_functionality = isset($license_info->disable_functionality) ? $license_info->disable_functionality : true;
?>
<div class="ds-wp-settings-api-ajax-form-row wpla_check_license-response-wrapper">
  <div id="wpla_check_license-response">
    <p>
      <strong>License Status:</strong> <?php echo $license_message;?>
      <strong>Expiration:</strong> <?php echo $license_expiration;?>
<?php if(!$license_valid && $disable_functionality){ ?>
        <input type="hidden" id="wpla_disable_functionality" value="true">
<?php } else { ?>
        <input type="hidden" id="wpla_disable_functionality" value="false">
<?php } ?>
    </p>
  </div>
  <div><button id="wpla_check_license" name="get_news" value=true>Check License</button></div>
</div>
