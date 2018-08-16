<?php
/*
 * WP License Agent License Entry
 *
 * Version 1.5.3
 *
 * https://dustysun.com
 *
 * Copyright 2018 Dusty Sun
 * Released under the GPLv2 license.
 * 
 */
namespace DustySun\WP_License_Agent\Client\v1_5;

if(!class_exists('DustySun\WP_License_Agent\Client\v1_5\License_Panel')) { 
    class License_Panel {
    
    private $update_settings;
    private $update_slug;
    private $refresh_on_valid;

    public function __construct($update_slug = '', $refresh_on_valid = true) {

        if($update_slug == '') {
            // see if the session vars are set

            wp_die('You must call License_Panel class with the array of update settings.');
        }

        $this->update_slug = $update_slug;
        $this->refresh_on_valid = $refresh_on_valid;

        $this->get_post_data();

        add_action( 'admin_enqueue_scripts', array( $this, 'register_generate_license_scripts' ));
    } // end function __construct 


    public function get_post_data(){

        if(!isset($_SESSION)) {
            session_start();
        } //end if(!isset($_SESSION))

        $nonce_action = basename(__FILE__);
        $nonce_key = $this->update_slug . '_wpnonce';

        // See if we received POST data, but make sure this is our check
        // license form so as not to mess up other processing of forms
        // within WordPress
        if ($_POST) {

            // Check for the key 
            if(isset($_POST[$this->update_slug . '_wpla_license_form']) && $_POST[$this->update_slug . '_wpla_license_form']) { 
                if ( empty($_POST[$nonce_key]) || ! wp_verify_nonce( $_POST[$nonce_key], $nonce_action ) ) {
                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit();  
                } // end if nonce

                //Assign to session variables
                $_SESSION[$this->update_slug . '_wpla_license_values_updated'] = true;
    
                //see if we received a POST input
                if(isset($_POST[$this->update_slug . '_wpla_license_email'])) {
                    //sanitize
                    $cleaned_value = sanitize_text_field($_POST[$this->update_slug . '_wpla_license_email']);
                    update_option($this->update_slug . '_wpla_license_email', $cleaned_value);
                } // end if 

                if(isset($_POST[$this->update_slug . '_wpla_license_key'])) {
                    //sanitize
                    $cleaned_value = sanitize_text_field($_POST[$this->update_slug . '_wpla_license_key']);
                    update_option($this->update_slug . '_wpla_license_key', $cleaned_value);
                } // end if 

                // check if this was called without Ajax. If so, run the license update
                // daily check so on reload it shows the license status.
                if(isset($_POST[$this->update_slug . '_wpla_license_form_ajax']) && $_POST[$this->update_slug . '_wpla_license_form_ajax'] == "false" ) {  
                    // set a session var to run the license check on reload
                    $_SESSION[$this->update_slug . '_run_wpla_license_check'] = true;
                } // end if 

                //Redirect to clear the post data
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();  

            } // end if isset $_POST[$this->update_slug . '_wpla_license_form'] 
        } // end if post
    }

    public function show_license_panel() {
        
        $nonce_action = basename(__FILE__);
        $nonce_key = $this->update_slug . '_wpnonce';

        //get license info
        $license_info = get_option($this->update_slug . '_daily_license_check', true);

        if(isset($_SESSION[$this->update_slug . '_wpla_license_values_updated']) && $_SESSION[$this->update_slug . '_wpla_license_values_updated']) {
            $updated_message_class = 'visible';
            $_SESSION[$this->update_slug . '_wpla_license_values_updated'] = false;
            unset($_SESSION[$this->update_slug . '_wpla_license_values_updated']);
        } else {
            $updated_message_class = 'hidden';
        }
        $license_message = isset($license_info->message) ? $license_info->message : 'Unknown';
        $license_expiration = isset($license_info->expiration) ? $license_info->expiration : 'Unknown';
        $license_valid = isset($license_info->valid) ? $license_info->valid : false;
        $disable_functionality = isset($license_info->disable_functionality) ? $license_info->disable_functionality : true;
        $license_email = get_option($this->update_slug . '_wpla_license_email', false); 
        $license_key = get_option($this->update_slug . '_wpla_license_key', false); 
        
        if(!$license_valid) {
            $license_valid_class = 'invalid';
        } else {
            $license_valid_class = 'valid';
        }

        // Register admin scripts
        add_action( 'admin_enqueue', array( $this, 'register_generate_license_scripts' ), 1, 1 );

        ob_start();
        require dirname( __FILE__ ) . '/includes/generate-license.php';

        $license_panel = ob_get_contents();

        ob_end_clean();
        return $license_panel;

    } //end function generate_license_inputs

    public function show_license_lightbox( $update_title = '') {

        if($update_title != '') {
            $update_title = '<h1>' . $update_title . '</h1>';
        } // end if

        // get the literal HTML of the containing wrapper (div class)
        $ds_wpla_lightbox_output_inner_html = '
            <div id="' . $this->update_slug . '-wpla-lightbox-modal" class="wpla-lightbox-modal">
                <div class="wpla-lightbox-modal-box">
                  <div class="wpla-lightbox-close"><span class="dashicons dashicons-dismiss"></span></div><!--wpla-lightbox-close-->
                  <div class="wpla-lightbox-modal-inner">
                    <div class="wpla-lightbox-modal-content">
                    <div class="wpla-lightbox-title">' . $update_title . '</div>';
                    $ds_wpla_lightbox_output_inner_html .= License_Panel::show_license_panel($this->update_slug, true);
                $ds_wpla_lightbox_output_inner_html .= '</div>
                  </div> <!--wpla-lightbox-modal-inner-->
                </div> <!--wpla-lightbox-modal-box-->
                <div class="wpla-lightbox-modal-background"></div> <!--wpla-lightbox-modal-background-->
            </div>
            ';
        return $ds_wpla_lightbox_output_inner_html;

    } // end static function show_license_lightbox
    
    public function register_generate_license_scripts() {
        wp_enqueue_script( 'wpla-generate-license-1_5', WPLA_Client_Factory::get_updater_url( '/inc/includes/generate-license.js' ), '', false, true );
        wp_enqueue_style('wpla-generate-license-1_5', WPLA_Client_Factory::get_updater_url('/inc/includes/generate-license.css'));
    } // end function register_generate_license_scripts
    
}} // end class
