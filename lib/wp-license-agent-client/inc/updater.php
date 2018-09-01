<?php
/*
 * WP License Agent Update Checker Plugin & Theme Updater
 *
 * Version 1.5.6
 *
 * https://dustysun.com
 *
 * Copyright 2018 Dusty Sun
 * Released under the GPLv2 license.
 * Builds and extends plugin-update-checker libraries from Janis Elsts:
 * https://github.com/YahnisElsts/plugin-update-checker
 * 
 * Define these in wp-config.php for debugging or to set a different URL for updates
 * define( 'WP_LICENSE_AGENT_DEBUG', true );
 * define( 'WP_LICENSE_AGENT_DEVELOPMENT_VERSIONS', true );
 * define( 'WP_LICENSE_AGENT_TEST_URL' , 'https://your_alternate_url');
 */

namespace DustySun\WP_License_Agent\Client\v1_5;

require( dirname( __FILE__ ) . '/plugin-update-checker/plugin-update-checker.php');

if(!class_exists('DustySun\WP_License_Agent\Client\v1_5\Licensing_Agent')) {
class Licensing_Agent {

  private $update_settings;
  private $update_url;
  private $checklicense_url;
  private $license_panel;
  private $product_type;

  public function __construct($settings = null) {

    // set up the update settings
    $this->set_update_settings($settings);
    // create the update checker
    $this->build_wpla_update_checker();
    // see if the current license info should be retrieved
    $this->run_conditional_license_update_check();
    // see if we're a plugin or theme 
    $this->product_type = WPLA_Client_Factory::get_product_type(__DIR__);

    // generate the license panel class
    $this->license_panel = new License_Panel($this->update_settings['update_slug']);

    if($this->update_settings['puc_errors']) {
      add_action( 'admin_notices', array($this, 'show_puc_admin_error') );
    } //end if

    if($this->update_settings['license_errors']) {
      add_action( 'admin_notices', array($this, 'show_general_admin_notices') );
    } // end if

    add_action( 'admin_notices', array($this, 'show_license_error_banner') );

    add_action( 'admin_enqueue_scripts', array( $this, 'register_update_checker_scripts' ));

    if($this->product_type == 'plugin' || $this->product_type == 'mu-plugin') {
      add_filter('plugin_action_links_' . plugin_basename($this->update_settings['main_file']), array($this, 'wpla_show_plugin_license_link'), 9, 2);
      add_action('admin_footer-plugins.php', array($this, 'wpla_plugins_show_license_lightbox'));
    } // end if plugin

    add_filter( 'puc_request_info_result-' . $this->update_settings['update_slug'], array($this, 'update_checker_info_result') );

    add_action( 'wp_ajax_wpla_dismiss_admin_notice-' . $this->update_settings['update_slug'],  array($this, 'wpla_dismiss_admin_notice') );

    // register the cron callback
    add_action($this->update_settings['update_slug'] . '_daily_license_check', array($this, 'retrieve_product_license_info'));

    if (! wp_next_scheduled ( $this->update_settings['update_slug'] . '_daily_license_check' )) {
      wp_schedule_event(time(), 'daily', $this->update_settings['update_slug'] . '_daily_license_check');
   }

    //allow the REST license check to be done via AJAX
    add_action('wp_ajax_retrieve_license_info-' . $this->update_settings['update_slug'] , array($this, 'retrieve_product_license_info_ajax_handler'));

    if(isset($this->update_settings['news_widget']) && $this->update_settings['news_widget']) {   
      // Register the new dashboard widget with the 'wp_dashboard_setup' action
      add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets') );
    } // end if 

    //allow setting the license changed to valid transient via ajax 
    add_action('wp_ajax_set_license_changed_to_valid_transient-' . $this->update_settings['update_slug'] , array($this, 'set_license_changed_to_valid_transient_ajax_handler'));

    register_deactivation_hook($this->update_settings['main_file'], array($this, 'wpla_deactivation_hook'));

  } //end function __construct

  public function run_conditional_license_update_check() {
    if(!isset($_SESSION)) {
      session_start();
    } //end if(!isset($_SESSION))

    if(isset($_SESSION[$this->update_settings['update_slug'] . '_run_wpla_license_check']) && $_SESSION[$this->update_settings['update_slug'] . '_run_wpla_license_check'] ) {
      // retrieve updated license info
      $this->retrieve_license_info();

      unset($_SESSION[$this->update_settings['update_slug'] . '_run_wpla_license_check']);
    } // end if isset
  } // end function run_conditional_license_update_check

  public function set_update_settings ($settings) {

    // Do some error checking
    if($settings == null) {
      wp_die('You must pass a settings array to the WP License Agent update checker. Please check the documentation.');
    } else if(!isset($settings['update_url']) || $settings['update_url'] == '') {
      wp_die('You must pass a valid update_url in the settings array when setting up the WP License Agent update checker. Please check the documentation.');
    } else if(!isset($settings['update_slug']) || $settings['update_slug'] == '') {
      wp_die('You must pass a valid update_slug in the settings array when setting up the WP License Agent update checker. Please check the documentation.');
    } else if(!isset($settings['main_file']) || $settings['main_file'] == '') {
      wp_die('You must pass a valid main_file to the name of the main plugin php file or a file in the theme in the settings array when setting up the WP License Agent update checker. Please check the documentation. Usually you can just pass __FILE__ to the function.');
    }

    // store the settings in the class variable
    $this->update_settings = array(
      'update_url' => $settings['update_url'],
      'update_slug' => $settings['update_slug'],
      'main_file' => $settings['main_file'],
      'license' => isset($settings['license']) && !empty($settings['license']) ? $settings['license'] : get_option($settings['update_slug'] . '_wpla_license_key', false),
      'email' => isset($settings['email']) && !empty($settings['email']) ? $settings['email'] : get_option($settings['update_slug'] . '_wpla_license_email', false),
      'news_widget' => isset($settings['news_widget']) && is_bool($settings['news_widget']) ? $settings['news_widget'] : true,
      'puc_errors' => isset($settings['puc_errors']) && is_bool($settings['puc_errors']) ? $settings['puc_errors'] : true,
      'license_errors' => isset($settings['license_errors']) && is_bool($settings['license_errors']) ? $settings['license_errors'] : true,
    );

    // see if development versions are selected
    if(isset($this->update_settings['development']) && !empty($this->update_settings['development']) && is_bool($this->update_settings['development'])) {
      $development_versions = $this->update_settings['development'];
    } else if (defined('WP_LICENSE_AGENT_DEVELOPMENT_VERSIONS') ) {
      $development_versions = WP_LICENSE_AGENT_DEVELOPMENT_VERSIONS;
    } else {
      $development_versions = false;
    } // end if

    // check if a development flag is set
    if(defined('WP_LICENSE_AGENT_TEST_URL') && WP_LICENSE_AGENT_TEST_URL !=
    '') {
      $this->update_settings['update_url'] = WP_LICENSE_AGENT_TEST_URL;
    } // end if
    
    // construct the checklicense and license info URLs

    $this->update_settings['checklicense_url'] = $this->update_settings['update_url'] . '/wp-json/wp-license-agent/v1/checklicense/?update_slug=' . $this->update_settings['update_slug'] . '&license=' . $this->update_settings['license'] . '&email=' . $this->update_settings['email'] . '&url=' . site_url();

    $this->update_settings['updateserver_url'] = $this->update_settings['update_url'] . '/wp-json/wp-license-agent/v1/updateserver/?update_action=get_metadata&update_slug=' . $this->update_settings['update_slug'] . '&license=' . $this->update_settings['license'] . '&email=' . $this->update_settings['email'] . '&url=' . site_url() . '&development=' . $development_versions;

  } // end function set_update_settings

  public function update_checker_info_result($request) {
    if(isset($request->license_error)) {
      if($request->license_error) {
        update_option($this->update_settings['update_slug'] . '_puc_error', $request);
      }
    } else {
      //clear any db error
      update_option($this->update_settings['update_slug'] . '_puc_error', '');
    }
    return $request;
  } // end function  update_checker_info_result

  public function build_wpla_update_checker() {

    WPLA_Client_Factory::wl('This message shows because you have WP License Agent debug turned on - Update URL: ' . $this->update_settings['updateserver_url']);

    $myUpdateChecker = \Puc_v4p4_Factory::buildUpdateChecker(
      $this->update_settings['updateserver_url'],
      $this->update_settings['main_file'],
      $this->update_settings['update_slug']
    );
  } // end function build_wpla_update_checker
  
  public function retrieve_license_info() {
      
    $request = wp_remote_get( $this->update_settings['checklicense_url'] );

    if( is_wp_error( $request )) {
      return false;
    }
    $body = wp_remote_retrieve_body($request);

    $data = json_decode($body);

    if(isset($data)) {
      update_option($this->update_settings['update_slug'] . '_daily_license_check', $data);
    }
    if(isset($data->valid)) {
      update_option($this->update_settings['update_slug'] . '_license_validity', $data->valid);
    }
    if( (isset($data->code) && $data->code == 'rest_no_route') || !isset($data->message)) {
      $data->message = 'Error: Unable to retrieve license info.';
    }

    return($data);

  } // end function retrieve_license_info

  public function register_update_checker_scripts() {
    wp_enqueue_script( 'wpla-updater-1_5', WPLA_Client_Factory::get_updater_url('/inc/js/updater.js'), '', false, true );
  } // end register_update_checker_scripts

  // Clear the puc error message for this plugin or theme
  public function wpla_dismiss_admin_notice( $type = '') {
    if(isset($_POST['type']) && $_POST['type'] != ''){
      // Pick up the notice "type" - passed via jQuery (the "data-notice" attribute on the notice)
      $type = $_POST['type'];
    } else if( $type == '') {
      return;
    } // end if

    // update with either the type passed to the function or use the POST value
    update_option($type, '');
  } //end function wpla_dismiss_admin_notice

  // Function that outputs the contents of the dashboard widget
  public function dashboard_widget_function($post, $callback_args) {
    if($callback_args['args']->valid) {
      echo '<p><strong>License Status: </strong>  Active</p>';
      echo '<p><strong>Expires: </strong> ' . $callback_args['args']->expiration . '</p>';
    } else {
      // default text
      echo '<p><strong>License Status: </strong>  None - you are not licensed</p>';
      if(isset($callback_args['args']->expiration)) {
        echo '<strong>License expired on ' . $callback_args['args']->expiration . '.';
      }
    }
    if(isset($callback_args['args']->customer_message) && $callback_args['args']->customer_message != '' ) {
      echo '<hr>';
      echo $callback_args['args']->customer_message;
    }

    if(isset($callback_args['args']->license_message) && $callback_args['args']->license_message != '' ) {
      echo '<hr>';
      echo $callback_args['args']->license_message;
    }
  } // end function dashboard_widget_function

  // Function used in the action hook
  public function add_dashboard_widgets() {

    $data = get_option($this->update_settings['update_slug'] . '_daily_license_check', true);

    $product_name = $this->get_product_name();
    
    wp_add_dashboard_widget('dashboard_widget_' . $this->update_settings['update_slug'], $product_name . ' News', array($this,'dashboard_widget_function'), '', $data);

  } // end function add_dashboard_widgets



  public function wpla_deactivation_hook() {
    $timestamp = wp_next_scheduled ( $this->update_settings['update_slug'] . '_daily_license_check' );
    if ($timestamp) {
      wp_unschedule_event($timestamp, $this->update_settings['update_slug'] . '_daily_license_check');
    }
  } // end function wpla_deactivation_hook

  public function retrieve_product_license_info() {

    // $this->checklicense_url = $this->update_settings['update_url'] . '/wp-json/wp-license-agent/v1/checklicense/?update_slug=' . $this->update_settings['update_slug'] . '&license=' . $this->update_settings['license'] . '&email=' . $this->update_settings['email'] . '&url=' . site_url();
    $request = wp_remote_get( $this->update_settings['checklicense_url']  );

    if( is_wp_error( $request )) {
      return false;
    }
    $body = wp_remote_retrieve_body($request);

    $data = json_decode($body);

    update_option($this->update_settings['update_slug'] . '_daily_license_check', $data);
    update_option($this->update_settings['update_slug'] . '_license_validity', $data->valid);

    return($data);

  } // end function retrieve_product_license_info

  public function show_puc_admin_error() {
    $puc_error = get_option($this->update_settings['update_slug'] . '_puc_error', true);

    if(isset($puc_error->license_error)) {

      $current_version = $this->get_product_version();

      # show the error if the current version is lesser than what's available
      if( $puc_error->version > $current_version ) {
        $error_message = $puc_error->license_error;

        if(!is_array($error_message) && $error_message != '' && $error_message != null && $error_message != '1' && $error_message != 1) {
          printf( '<div class="notice-wpla notice notice-error is-dismissible" data-update-slug="' . $this->update_settings['update_slug'] . '" data-type="_puc_error"><p>' . $error_message . '</p></div>');
        } //end if($error_message != '' || $error_message != null)
      } // end if($puc_error['version'] > $current_version ) 
    } // end if(isset($puc_error->license_error)) 
  } //end functionshow_puc_admin_error

  public function show_license_error_banner() {
    // check the license validity
		$license_info = get_option($this->update_settings['update_slug'] . '_daily_license_check', true);

    // get the license info if it's blank
    if($license_info == '') {
      $this->retrieve_product_license_info();
      $license_info = get_option($this->update_settings['update_slug'] . '_daily_license_check', true);
    } // end if ($license_info == '')

		if( isset($license_info->valid ) && !$license_info->valid ) {
			$license_message = isset($license_info->message) ? $license_info->message : 'Invalid license.';

      $product_name = $this->get_product_name();

			echo '<div class="error"><p><strong>' . $product_name . ' License Error:</strong> ' . $license_info->message . ' Please check your <a href="#" class="wpla-update-license-lightbox" data-update-slug="' . $this->update_settings['update_slug'] . '">license settings</a>.</p>';
			if( isset($license_info->valid ) && $license_info->disable_functionality ){
				echo '<p>Features have been disabled.</p>';
			}
      echo '</div>';
      
      $license_panel_lightbox = $this->license_panel->show_license_lightbox( $this->get_product_name() . ': License Information');

      echo $license_panel_lightbox;

		} // end if( isset($license_info->valid ) && !$license_info->valid )
  } // end function show_license_error_banner

  private function get_product_name() {
    if($this->product_type == 'plugin' || $this->product_type == 'mu-plugin') {
        $product_name = get_plugin_data($this->update_settings['main_file'])['Name'];
    } else if($this->product_type == 'theme' || $this->product_type == 'child-theme') { 
        $product_name = wp_get_theme($this->update_settings['update_slug'])['Name'];
    } // end if 
    return $product_name;
  } // end function get_product_name 

  private function get_product_version() {
    if($this->product_type == 'plugin' || $this->product_type == 'mu-plugin') {
        $product_version = get_plugin_data($this->update_settings['main_file'])['Version'];
    } else if($this->product_type == 'theme' || $this->product_type == 'child-theme') { 
        $product_version = wp_get_theme($this->update_settings['update_slug'])['Version'];
    } // end if 
    return $product_version;
  } // end function get_product_version 

  // Admin notice
	public function show_general_admin_notices($var) {

		if( get_transient( $this->update_settings['update_slug'] . '_license_changed_to_valid' ) ) {
			echo '<div class="notice notice-success"><p>' . __( 'Thanks for updating your license for ' . $this->get_product_name() . '.' , 'ds_wpla' ) . '</p></div>';
			delete_transient( $this->update_settings['update_slug'] . '_license_changed_to_valid' );
    } // end if 
    
    // if it's a theme 
    if($this->product_type == 'theme' || $this->product_type == 'child-theme') { 
      if(get_current_screen()->base == 'themes') {
        
        $show_license_panel = $this->license_panel->show_license_panel();
        
        echo '<div class="notice"><h1>' . __( $this->get_product_name() . ' License Info' , 'ds_wpla' ) .'</h1>' . __('<p>Enter your license key or check the status of your key in the form below.</p>', 'ds_wpla') . $show_license_panel . '<p></p></div>';
      } // end if 
    } // end if theme 

  } //end function show_general_admin_notices
  
  public function set_license_changed_to_valid_transient_ajax_handler() {
    if(isset($_POST['update_transient']) && $_POST['update_transient'] == true) {
      // clear any puc errors
      $this->wpla_dismiss_admin_notice($this->update_settings['update_slug'] . '_puc_error');
      set_transient($this->update_settings['update_slug'] . '_license_changed_to_valid', 1);
      $json_output = array(
        'message' => 'Transient ' . $this->update_settings['update_slug'] . '_license_changed_to_valid was set'
      );
      wp_send_json($json_output);
      wp_die();
    } //end if
  } // end set_license_changed_to_valid_transient

  public function retrieve_product_license_info_ajax_handler() {
    if(isset($_POST['get_license_info']) && $_POST['get_license_info'] == true) {
      $json_output = array(
        'license_data' => $this->retrieve_license_info(),
        'updateserver_url' => $this->update_settings['updateserver_url'],
        'checklicense_url' => $this->update_settings['checklicense_url']
      );
      wp_send_json($json_output);
  		wp_die();
    }
  } //end function retrieve_product_license_info_ajax_handler

  public function wpla_show_plugin_license_link($links, $plugin) {
    $links[] = '<a href="#' . $this->update_settings['update_slug'] . '-wpla-update-license-lightbox" class="wpla-update-license-lightbox" data-update-slug="' . $this->update_settings['update_slug'] . '">Update License</a>';
    return $links;
  } // end function wpla_show_plugin_license_link
  
  public function wpla_plugins_show_license_lightbox() {
    // generate the lightbox
    $license_panel_lightbox = $this->license_panel->show_license_lightbox( $this->get_product_name() . ': License Information');

    echo $license_panel_lightbox ;
  } // end function wpla_plugins_show_license_lightbox
  

}} //end class
