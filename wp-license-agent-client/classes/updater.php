<?php
/*
 * WP License Agent Update Checker Plugin & Theme Update Library
 *
 * Version 1.4.2
 *
 * https://dustysun.com
 *
 * Copyright 2018 Dusty Sun
 * Released under the GPLv2 license.
 * Builds and extends plugin-update-checker libraries from Janis Elsts:
 * https://github.com/YahnisElsts/plugin-update-checker
 *
 * USAGE:
 * require this file in your plugin or theme (and make sure the plugin-update-checker
 * directory is in the same directory as this file).
 *
 * Create a settings array with the following info:

   $settings = array(
     'update_url' => 'https://your-update-server.com/updates', // server address and wp-update-server path
     'update_slug' => your-plugin-slug or name of your theme directory, // needs to be the same as the update-slug on the server
     'main_file' => __FILE__, // server path to the main plugin file or any file in the theme
     'license' => $license_key, // your plugin or theme should allow the user to enter their license key. Retrieve that from your user settings and enter it here.
     'email' => $email_address, // your plugin or theme should allow the user to enter their email address. Retrieve that from your user settings and enter it here.
     'development' => set to true or false. If you leave it off, it will be set to false. True and you will receive a development version of the plugin or theme update if defined. If the constant  is set to true in wp-config.php this will be used.
   );

   finally, instantiate this object from your plugin:

     use \DustySun\WP_License_Agent\Updater\v1_4 as WPLA;
     require_once( dirname( __FILE__ ) . '/lib/wp-license-agent-client/updater.php');

     $wpla_settings = array(
       'update_url' => 'server URL goes here',
       'update_slug' => 'slug / name of directory of plugin or theme',
       'main_file' => __FILE__,
       'license' => 'license key from the user goes here',
       'email' => 'email address from the user goes here',
       'news_widget' => true, // show plugin/theme news on the dashboard
       'puc_errors' => true // show update checker errors
     );

     $wpla_update_checker = new WPLA\Licensing_Agent($wpla_settings);

 */

namespace DustySun\WP_License_Agent\Updater\v1_4;

require( dirname( __FILE__ ) . '/plugin-update-checker/plugin-update-checker.php');

if(!class_exists('DustySun\WP_License_Agent\Updater\v1_4\Licensing_Agent')) {
class Licensing_Agent {

  private $update_settings;
  private $update_url;
  private $checklicense_url;

  public function __construct($settings = null) {
    // set up the update settings
    $this->set_update_settings($settings);
    // create the update checker
    $this->build_wpla_update_checker();

    if($this->update_settings['puc_errors']) {
      add_action( 'admin_notices', array($this, 'show_puc_admin_error') );
    }

    if($this->update_settings['license_errors']) {
      add_action( 'admin_notices', array($this, 'show_license_error_banner') );
    }

    add_filter('plugin_action_links_' . plugin_basename($this->update_settings['main_file']), array($this, 'wpla_show_plugin_license_link'), 9, 2);
    add_action('admin_footer-plugins.php', array($this, 'wpla_plugins_show_license_lightbox'));

    add_action( 'admin_print_scripts', array($this, 'print_update_checker_scripts'), 100, 10 );

    add_filter( 'puc_request_info_result-' . $this->update_settings['update_slug'], array($this, 'update_checker_info_result') );

    add_action( 'wp_ajax_wpla_dismiss_puc_error-' . $this->update_settings['update_slug'],  array($this, 'wpla_dismiss_puc_error') );

    //allow the REST license check to be done via cron
    add_action('wp_ajax_retrieve_news-' . $this->update_settings['update_slug'] , array($this, 'retrieve_product_news_ajax_handler'));

    if(isset($this->update_settings['news_widget']) && $this->update_settings['news_widget']) {
      // add an activation hook that schedules a cron job to get news
      register_activation_hook($this->update_settings['main_file'], array($this, 'mluc_activation_hook'));

      // register the cron callback
      add_action($this->update_settings['update_slug'] . '_daily_news_check', array($this, 'retrieve_product_news'));

      // Register the new dashboard widget with the 'wp_dashboard_setup' action
      add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets') );
    }

    register_deactivation_hook($this->update_settings['main_file'], array($this, 'mluc_deactivation_hook'));

  } //end function __construct
  protected function wl ( $log )  {
      // if(defined('WP_LICENSE_AGENT_DEBUG')) {
        // if ( true === WP_LICENSE_AGENT_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        // }
      // }
  } // end function wl

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

    // check if a development flag is set
    if(defined('WP_LICENSE_AGENT_TEST_URL') && WP_LICENSE_AGENT_TEST_URL !=
    '') {
      $this->update_settings['update_url'] = WP_LICENSE_AGENT_TEST_URL;
    } // end if

  } // end function set_update_settings
  public function show_puc_admin_error() {
    $error_message = get_option($this->update_settings['update_slug'] . '_puc_error', true);

    if(!is_array($error_message) && $error_message != '' && $error_message != null && $error_message != '1' && $error_message != 1) {
      printf( '<div class="notice-mluc notice notice-error is-dismissible" data-notice="' . $this->update_settings['update_slug'] . '_puc_error"><p>' . $error_message . '</p></div>');
    } //end if($error_message != '' || $error_message != null)
  } //end functionshow_puc_admin_error

  public function update_checker_info_result($request) {
    if(isset($request->license_error)) {
      if($request->license_error) {
        update_option($this->update_settings['update_slug'] . '_puc_error', $request->license_error);
        // add_action( 'admin_notices', array($this, 'show_puc_admin_error'));
      }
    } else {
      //clear any db error
      update_option($this->update_settings['update_slug'] . '_puc_error', '');
    }
    return $request;
  } // end function  update_checker_info_result

  protected function build_wpla_update_checker() {

    // see if development versions are selected
    if(isset($this->update_settings['development']) && !empty($this->update_settings['development']) && is_bool($this->update_settings['development'])) {
      $development_versions = $this->update_settings['development'];
    } else if (defined('WP_LICENSE_AGENT_DEVELOPMENT_VERSIONS') ) {
      $development_versions = WP_LICENSE_AGENT_DEVELOPMENT_VERSIONS;
    } else {
      $development_versions = false;
    } // end if

    // build the update URL
    $deprecated_update_url = $this->update_settings['update_url'] . '/wp-license-agent/?update_action=get_metadata&update_slug=' . $this->update_settings['update_slug'] . '&license=' . $this->update_settings['license'] . '&email=' . $this->update_settings['email'] . '&url=' . site_url() . '&development=' . $development_versions;

    $this->update_url = $this->update_settings['update_url'] . '/wp-json/wp-license-agent/v1/updateserver/?update_action=get_metadata&update_slug=' . $this->update_settings['update_slug'] . '&license=' . $this->update_settings['license'] . '&email=' . $this->update_settings['email'] . '&url=' . site_url() . '&development=' . $development_versions;

    $this->wl('This message shows because you have WP License Agent debug turned on - Update URL: ' . $this->update_url);

    $myUpdateChecker = \Puc_v4p4_Factory::buildUpdateChecker(
      $this->update_url,
      $this->update_settings['main_file'],
      $this->update_settings['update_slug']
    );
  } // end function build_wpla_update_checker

  public function print_update_checker_scripts() {
    ?>
      <script type="text/javascript">
        jQuery(function($) {
        // Hook into the "notice-mluc" class we added to the notice, so
        // Only listen to YOUR notices being dismissed
        $( document ).on( 'click', '.notice-mluc .notice-dismiss', function () {
            // Read the "data-notice" information to track which notice
            // is being dismissed and send it via AJAX
            var type = $( this ).closest( '.notice-mluc' ).data( 'notice' );
            console.log(type);
            // Make an AJAX call
            // Since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            $.ajax( ajaxurl,
              {
                type: 'POST',
                data: {
                  action: 'wpla_dismiss_puc_error-<?php echo $this->update_settings['update_slug']; ?>',
                  type: type,
                }
              } );
          } );
        });
      </script>
    <?php
  } //end function

  // Clear the puc error message for this plugin or theme
  public function wpla_dismiss_puc_error() {
    // Pick up the notice "type" - passed via jQuery (the "data-notice" attribute on the notice)
    $type = $_POST['type'];
    update_option($type, '');
  } //end function wpla_dismiss_puc_error

  // Function that outputs the contents of the dashboard widget
  public function dashboard_widget_function($post, $callback_args) {
    if($callback_args['args']->valid) {
      echo '<p><strong>License Status: </strong>  Active</p>';
      echo '<p><strong>Expires: </strong> ' . $callback_args['args']->expiration . '</p>';
    } else {
      echo '<p><strong>License Status: </strong>  None - you are not licensed</p>';
      if(isset($callback_args['args']->expiration)) {
        echo '<strong>License expired on ' . $callback_args['args']->expiration . ' - please update your license to continue to get support and updates!';
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

    $data = get_option($this->update_settings['update_slug'] . '_daily_news_check', true);

    $product_name = $this->get_product_name();
    
    wp_add_dashboard_widget('dashboard_widget_' . $this->update_settings['update_slug'], $product_name . ' News', array($this,'dashboard_widget_function'), '', $data);

  } // end function add_dashboard_widgets

  public function get_product_name() {
    $product_name = get_plugin_data($this->update_settings['main_file'])['Name'];

    if($product_name == '') {
      $product_name = wp_get_theme($this->update_settings['update_slug'])['Name'];
    }

    return $product_name;
  } // end function get_product_name 

  public function mluc_activation_hook() {
    if (! wp_next_scheduled ( $this->update_settings['update_slug'] . '_daily_news_check' )) {
	     wp_schedule_event(time(), 'daily', $this->update_settings['update_slug'] . '_daily_news_check');
    }
  } // end function mluc_activation_hook

  public function mluc_deactivation_hook() {
    $timestamp = wp_next_scheduled ( $this->update_settings['update_slug'] . '_daily_news_check' );
    if ($timestamp) {
      wp_unschedule_event($timestamp, $this->update_settings['update_slug'] . '_daily_news_check');
    }
  } // end function mluc_deactivation_hook

  public function retrieve_product_news() {

    $this->checklicense_url = $this->update_settings['update_url'] . '/wp-json/wp-license-agent/v1/checklicense/?update_slug=' . $this->update_settings['update_slug'] . '&license=' . $this->update_settings['license'] . '&email=' . $this->update_settings['email'] . '&url=' . site_url();
    $request = wp_remote_get( $this->checklicense_url );

    if( is_wp_error( $request )) {
      return false;
    }
    $body = wp_remote_retrieve_body($request);

    $data = json_decode($body);

    update_option($this->update_settings['update_slug'] . '_daily_news_check', $data);
    update_option($this->update_settings['update_slug'] . '_license_validity', $data->valid);

    return($data);

  } // end function retrieve_product_news

  public function show_license_error_banner() {
    // check the license validity
		$license_info = get_option($this->update_settings['update_slug'] . '_daily_news_check', true);

		if( isset($license_info->valid ) && !$license_info->valid ) {
			$license_message = isset($license_info->message) ? $license_info->message : 'Invalid license.';

      $product_name = $this->get_product_name();

			echo '<div class="error"><p><strong>' . $product_name . ' License Error:</strong> ' . $license_info->message . ' Please check your <a href="#" class="wpla-update-license-lightbox" data-update-slug="' . $this->update_settings['update_slug'] . '">license settings</a>.</p>';
			if( isset($license_info->valid ) && $license_info->disable_functionality ){
				echo '<p>Features have been disabled.</p>';
			}
      echo '</div>';
      
      // generate the lightbox
      echo License_Panel::show_license_lightbox($this->update_settings['update_slug'], $this->get_product_name() . ': License Information');

		} // end if( isset($license_info->valid ) && !$license_info->valid )
  } // end function show_license_error_banner
  public function retrieve_product_news_ajax_handler() {
    if(isset($_POST['get_news']) && $_POST['get_news'] == true) {
      $json_output = array(
        'news_data' => $this->retrieve_product_news(),
        'update_url' => $this->update_url,
        'checklicense_url' => $this->checklicense_url
      );
      wp_send_json($json_output);
  		wp_die();
    }
  } //end function retrieve_product_news_ajax_handler

  public function wpla_show_plugin_license_link($links, $plugin) {
    $links[] = '<a href="#' . $this->update_settings['update_slug'] . '-wpla-update-license-lightbox" class="wpla-update-license-lightbox" data-update-slug="' . $this->update_settings['update_slug'] . '">Update License</a>';
    return $links;
  } // end function wpla_show_plugin_license_link
  
  public function wpla_plugins_show_license_lightbox() {
  // generate the lightbox
    echo License_Panel::show_license_lightbox($this->update_settings['update_slug'], $this->get_product_name() . ': License Information');

  }
  public function custom_plugin_row_meta($meta, $file, $data, $status) { 
    //
    // do some filtering on the version text of the Akismet plugin
    //
    // $this->wl($this->update_settings['update_slug'] . '/' . $this->update_settings['main_file']);
    // if($file == $this->update_settings['update_slug'] . '/' . $this->update_settings['main_file']) {
    //   $this->wl('heels');
    // }
    if( $file === 'akismet/akismet.php' )
        // $meta[0] = "Version 3.14159265359"; // BAD
        $meta[0] = sprintf( '<strong>%s</strong>', $meta[0] ); // BETTER

    return $meta;
}
}} //end class
