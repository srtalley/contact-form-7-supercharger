
<style>
    .wpla-check-license-panel * {
        box-sizing: border-box;
    }
    .wpla-check-license-panel {
        background-color: #F1F5F9;
    }
    .wpla-check-license-response-wrapper {
        background-color: #535d67;
        color: #fff;
        padding: 0 10px;
        width: 100%;
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        justify-content: space-between;
    }
    .wpla-check-license-response-wrapper button {
        margin: 10px 0;
    }
    button.wpla-check-license {
        transition: all 0.4s ease-in-out;
        background-color: #fff;
        border: none;
        padding: 8px 20px;
        font-size: 16px;
        color: #535d67;
        font-weight: bold;
        border: 2px solid #fff;
        margin-right: 1px;
    }

    button.wpla-check-license:focus {
        outline: -webkit-focus-ring-color none;
        outline-color: -webkit-focus-ring-color;
        outline-style: none;
        outline-width: 0;
    }
    .wpla-check-license-response-wrapper button.wpla-check-license:hover,
    .wpla-check-license-response-wrapper.invalid button.wpla-check-license:hover,
    .wpla-check-license-response-wrapper.valid button.wpla-check-license:hover {
        background-color: rgba(0, 0, 0, 0);
        color: #fff;
    }
    .wpla-check-license-response-wrapper.invalid {
        background-color: #9c0000;
    }
    .wpla-check-license-response-wrapper.invalid button.wpla-check-license {
        color: #9c0000;
    }
    .wpla-check-license-response-wrapper.valid {
        background-color: #005019;
    }
    .wpla-check-license-response-wrapper.valid button.wpla-check-license {
        color: #005019;
    }
    .wpla-check-license-response-wrapper .dashicons {
        width: 24px;
        height: 24px;
        font-size: 28px;
    }

    .wpla-check-license-response-wrapper.valid .dashicons-warning {
        display:none;
    }
    .wpla-check-license-response-wrapper.invalid .dashicons-yes {
        display:none;
    }
    .wpla-check-license-response-wrapper p {
        font-size: 1.3em;
        font-family: 'Open Sans', sans-serif;
    }
    .wpla-check-license-panel .wpla-license-inputs,
    .wpla-check-license-panel .wpla-row {
        display: flex;
        flex-wrap: nowrap;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: 10px;

    }
    .wpla-check-license-panel .wpla-license-inputs {
        padding: 0;
    }
    .wpla-check-license-panel .wpla-row.wpla-email {
        flex: 1 1 80%;
        padding-right: 0;
    }
    .wpla-check-license-panel label {
        white-space: nowrap;
        font-weight: bold;
        font-size: 1.1em;
        margin-right: 10px;
        font-family: 'Open Sans', sans-serif;
    }
    .wpla-check-license-panel input[type="text"],
    .wpla-check-license-panel input[type="email"] {
        margin-left: 0;
        border-radius: 0;
        border: none;
        box-shadow: none;
        background: #E0E5EA;
        padding: 13px;
        font-size: 14px;
        color: #32373C;
        height: auto;
        width: auto;
        font-family: 'Open Sans', sans-serif;
        font-size: 1.1em;
        width: 100%;
        margin-bottom: 0;
        -webkit-transition: background .5s;
        -moz-transition: background .5s;
        transition: background .5s;
    }
    .wpla-check-license-panel input[type="text"]:focus,
    .wpla-check-license-panel input[type="email"]:focus {
        background: #cbd3da;
    }
    .wpla-license-values-updated {
        display:none;
    }
    .wpla-license-values-updated.visible {
        display: block;
        border-left: 5px solid green;
        background: #dff9f2;
        padding: 2px 30px;
    }
    /* Lightbox */
    .wpla-lightbox-modal {
        display:none;
    }
    .wpla-lightbox-modal.wpla-lightbox-modal-show-bg {
        display: block;
    }
    .wpla-lightbox-modal-box {
        opacity:0;
        transition: opacity 1s linear;
        position: fixed;
        z-index: 99999991;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        min-width: 310px;
        max-width: 1080px;
        background-color: #fff;
        padding: 20px;
        box-shadow: 1px 1px 5px 0px rgba(0, 0, 0, 0.5);
        color: #404040;
    }
    .wpla-lightbox-close {
        cursor: pointer;
        position: absolute;
        right: -15px;
        top: -15px;
        z-index: 99999992;
    }
    .wpla-lightbox-close .dashicons {
        font-size: 30px;
        width: auto;
        height: auto;
        background-color: #fff;
        border-radius: 100px;
        line-height: 29px;
        box-shadow: 1px 1px 5px 1px rgba(0, 0, 0, 0.51);
    }
    .wpla-lightbox-close .dashicons:hover {
        color: #a00000;
    }
    .wpla-lightbox-title {
        text-align: center;
    }
    .wpla-lightbox-title h1 {
        line-height: 1.1em;
        margin-top: 5px;
    }
    .wpla-lightbox-modal-additional-messages {
        display: none;
        opacity: 0;
        transition: opacity 1s linear;
        padding: 20px 10px 10px;
        border-top: 1px solid gray;
    }
    .wpla-lightbox-modal.wpla-lightbox-modal-show-message .wpla-lightbox-modal-box,
    .wpla-lightbox-modal .wpla-lightbox-modal-additional-messages.wpla-lightbox-modal-show-additional  {
        display: block;
        opacity: 1;
        transition: opacity 1s linear;
    }
    .wpla-lightbox-modal-additional-messages h4.wpla-lightbox-modal-redirecting {
        padding-bottom: 0;
        font-weight: 600;
    }
    .wpla-lightbox-modal-background {
        position: fixed;
        height: 100%;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 99999990;
        background-color: rgba(0, 0, 0, .4);
    }
    @media (max-width: 981px) {
        .wpla-check-license-panel .wpla-license-inputs {
            flex-wrap: wrap;
        }
        .wpla-check-license-panel label {
            min-width: 110px;
        }
        .wpla-check-license-panel .wpla-row.wpla-email {
            padding-right: 10px;
        }
    }
    @media (max-width: 525px) {
        .wpla-check-license-panel .wpla-row,
        .wpla-check-license-response-wrapper {
           flex-wrap: wrap;
        }
        .wpla-check-license-response-wrapper, .wpla-check-license-response-wrapper div {
            justify-content: center;
            text-align: center;
        }
        .wpla-check-license-response-wrapper button {
            margin-top: 0;
        }
        .wpla-lightbox-modal-box {
            padding: 5px;
        }
        .wpla-lightbox-close {
            right: 4px;
            top: -34px;
        }
        .wpla-lightbox-title h1 {
            font-size: 18px;
            margin: 0 0 5px;
        }
    }
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