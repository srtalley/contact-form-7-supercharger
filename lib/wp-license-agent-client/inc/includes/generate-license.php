<?php 
// v1.5
?>
<div class="wpla-license-values-updated <?php echo $updated_message_class; ?> ">
    <h4>Updated values saved</h4>
</div>
<div class="wpla-check-license-panel">
    <form action="" id="<?php echo $this->update_slug; ?>_wpla-license-entry" class="wpla-license-entry 1_5" data-update-slug="<?php echo $this->update_slug; ?>" data-wpla-version="1_5" method="POST">
        
        <?php wp_nonce_field( $nonce_action, $nonce_key ); ?>
        <input type="hidden" name="<?php echo $this->update_slug; ?>_wpla_license_form" value="true">
        <input type="hidden" name="<?php echo $this->update_slug; ?>_wpla_license_form_ajax" value="false">
        <?php if(!$license_valid && $disable_functionality){ ?>
            <input type="hidden" id="<?php echo $this->update_slug; ?>_wpla_disable_functionality" value="true">
        <?php } else { ?>
            <input type="hidden" id="<?php echo $this->update_slug; ?>_wpla_disable_functionality" value="false">
        <?php } ?>
        <?php if($this->refresh_on_valid && !$license_valid){ // refresh if the license wasn't valid to start with ?>
            <input type="hidden" id="<?php echo $this->update_slug; ?>_wpla_refresh_on_valid" value="true">
        <?php } else { ?>
            <input type="hidden" id="<?php echo $this->update_slug; ?>_wpla_refresh_on_valid" value="false">
        <?php } ?>
        <div class="wpla-license-inputs">
            <div class="wpla-row wpla-email"><label for="<?php echo $this->update_slug; ?>_wpla_license_email">Email Address: </label><input type="email" id="<?php echo $this->update_slug; ?>_wpla_license_email" name="<?php echo $this->update_slug; ?>_wpla_license_email" value="<?php echo $license_email; ?>" class="ds-wp-api-input "></div>
            <div class="wpla-row wpla-license"><label for="<?php echo $this->update_slug; ?>_wpla_license_key">License Key:</label><input type="text" id="<?php echo $this->update_slug; ?>_wpla_license_key" name="<?php echo $this->update_slug; ?>_wpla_license_key" value="<?php echo $license_key; ?>" class="ds-wp-api-input "></div>
        </div>
        
        <div class="wpla-check-license-response-wrapper <?php echo $license_valid_class; ?>">
            <div id="<?php echo $this->update_slug; ?>_wpla-check-license-response">
                <p><span class="dashicons dashicons-yes"></span> <span class="dashicons dashicons-warning"></span> 
                    <strong>License Status:</strong> <?php echo  $license_message; ?>
                        <strong>Expiration:</strong> <?php echo $license_expiration; ?>
                </p>
            </div>
        <div><button class="wpla-check-license" name="<?php echo $this->update_slug; ?>_wpla_get_license" value=true>Check License</button></div>
        </div>
    </form>
</div>