
<style>
    
</style>

<div class="wpla-check-license-panel">
    <form id="<?php echo $update_slug; ?>_wpla-license-entry" class="wpla-license-entry" data-update-slug="<?php echo $update_slug; ?>" method="POST">
        
        <?php wp_nonce_field( $nonce_action, $nonce_key ); ?>
        <input type="hidden" name="<?php echo $update_slug; ?>_wpla_license_form" value="true">
        <?php if(!$license_valid && $disable_functionality){ ?>
            <input type="hidden" id="<?php echo $update_slug; ?>_wpla_disable_functionality" value="true">
        <?php } else { ?>
            <input type="hidden" id="<?php echo $update_slug; ?>_wpla_disable_functionality" value="false">
        <?php } ?>
        <div class="wpla-license-values-updated <?php echo $updated_message_class; ?> ">
            <h4>Values updated</h4>
        </div>
        <div class="wpla-license-inputs">
            <div class="wpla-row wpla-email"><label for="<?php echo $update_slug; ?>_wpla_license_email">Email Address: </label><input type="email" id="<?php echo $update_slug; ?>_wpla_license_email" name="<?php echo $update_slug; ?>_wpla_license_email" value="<?php echo $license_email; ?>" class="ds-wp-api-input "></div>
            <div class="wpla-row wpla-license"><label for="<?php echo $update_slug; ?>_wpla_license_key">License Key:</label><input type="text" id="<?php echo $update_slug; ?>_wpla_license_key" name="<?php echo $update_slug; ?>_wpla_license_key" value="<?php echo $license_key; ?>" class="ds-wp-api-input "></div>
        </div>
        
        <div class="wpla-check-license-response-wrapper <?php echo $license_valid_class; ?>">
            <div id="<?php echo $update_slug; ?>_wpla-check-license-response">
                <p><span class="dashicons dashicons-yes"></span> <span class="dashicons dashicons-warning"></span> 
                    <strong>License Status:</strong> <?php echo  $license_message; ?>
                        <strong>Expiration:</strong> <?php echo $license_expiration; ?>
                </p>
            </div>
        <div><button class="wpla-check-license" name="get_news" value=true>Check License</button></div>
        </div>
        
    </form>
</div>