<?php 
namespace DustySun\WP_License_Agent\Updater\v1_4;

if(!class_exists('DustySun\WP_License_Agent\Updater\v1_4\License_Panel')) { 
    class License_Panel {
    
    static function show_license_panel($update_slug) {

        if($update_slug == '') {
            wp_die('You must call show_license_panel with an update slug.');
        }
        $nonce_action = basename(__FILE__);
        $nonce_key = $update_slug . '_wpnonce';

        //get license info
        $license_info = get_option($update_slug . '_daily_news_check', true);

        if(!isset($_SESSION)) {
            session_start();
        } //end if(!isset($_SESSION))

        // See if we received POST data, but make sure this is our check
        // license form so as not to mess up other processing of forms
        // within WordPress
        if ($_POST) {
            // Check for the key 
            if(isset($_POST[$update_slug . '_wpla_license_form']) && $_POST[$update_slug . '_wpla_license_form']) { 
            
                if ( empty($_POST[$nonce_key]) || ! wp_verify_nonce( $_POST[$nonce_key], $nonce_action ) ) {
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit();  
                } // end if nonce

                //Assign to session variables
                $_SESSION[$update_slug . '_wpla_license_values_updated'] = true;
    
                //see if we received a POST input
                if(isset($_POST[$update_slug . '_wpla_license_email'])) {
                    //sanitize
                    $cleaned_value = sanitize_text_field($_POST[$update_slug . '_wpla_license_email']);
                    update_option($update_slug . '_wpla_license_email', $cleaned_value);
                }
                if(isset($_POST[$update_slug . '_wpla_license_key'])) {
                    //sanitize
                    $cleaned_value = sanitize_text_field($_POST[$update_slug . '_wpla_license_key']);
                    update_option($update_slug . '_wpla_license_key', $cleaned_value);
                }
                //Redirect to clear the post data
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();  
            } // end if isset $_POST[$update_slug . '_wpla_license_form'] 
        } // end if post
        
        if(isset($_SESSION[$update_slug . '_wpla_license_values_updated']) && $_SESSION[$update_slug . '_wpla_license_values_updated']) {
            $updated_message_class = 'visible';
            $_SESSION[$update_slug . '_wpla_license_values_updated'] = false;
        } else {
            $updated_message_class = 'hidden';
        }
        $license_message = isset($license_info->message) ? $license_info->message : 'Unknown';
        $license_expiration = isset($license_info->expiration) ? $license_info->expiration : 'Unknown';
        $license_valid = isset($license_info->valid) ? $license_info->valid : false;
        $disable_functionality = isset($license_info->disable_functionality) ? $license_info->disable_functionality : true;
        $license_email = get_option($update_slug . '_wpla_license_email', false); 
        $license_key = get_option($update_slug . '_wpla_license_key', false); 
        
        if(!$license_valid) {
            $license_valid_class = 'invalid';
        } else {
            $license_valid_class = 'valid';
        }
        // Register admin scripts
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_ds_wp_settings_api_admin_styles_scripts' ), 1, 1 );

        ob_start();
        require dirname( __FILE__ ) . '/includes/generate-license.php';

        $license_panel = ob_get_contents();

        ob_end_clean();
        return $license_panel;

    } //end function generate_license_inputs

    static function show_license_lightbox($update_slug, $update_title = '') {
        
        if($update_slug == '') {
            wp_die('You must call show_license_lightbox with an update slug.');
        }
        
        if($update_title != '') {
            $update_title = '<h1>' . $update_title . '</h1>';
        }

        // get the literal HTML of the containing wrapper (div class)
        $ds_wpla_lightbox_output_inner_html = '
            <div id="' . $update_slug . '-wpla-lightbox-modal" class="wpla-lightbox-modal">
                <div class="wpla-lightbox-modal-box">
                  <div class="wpla-lightbox-close"><span class="dashicons dashicons-dismiss"></span></div><!--wpla-lightbox-close-->
                  <div class="wpla-lightbox-modal-inner">
                    <div class="wpla-lightbox-modal-content">
                    <div class="wpla-lightbox-title">' . $update_title . '</div>';
                    $ds_wpla_lightbox_output_inner_html .= License_Panel::show_license_panel($update_slug);
                $ds_wpla_lightbox_output_inner_html .= '</div>
                  </div> <!--wpla-lightbox-modal-inner-->
                </div> <!--wpla-lightbox-modal-box-->
                <div class="wpla-lightbox-modal-background"></div> <!--wpla-lightbox-modal-background-->
            </div>
            ';
        return $ds_wpla_lightbox_output_inner_html;

    } // end static function show_license_lightbox
    static function register_ds_wp_settings_api_admin_styles_scripts() {
        error_log('called');
        error_log(plugins_url( '/includes/generate-license.js', __FILE__ ));
        wp_enqueue_script( 'giggles', plugins_url( '/includes/generate-license.js', __FILE__ ), '', false, true );
    }
}} // end class
