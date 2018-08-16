<?php
namespace DustySun\CF7_Supercharger;
use \DustySun\WP_Settings_API\v2 as DSWPSettingsAPI;
class Enhanced_Contact_Form_7_Settings {

	private $ds_ewpcf7_plugin_hook;

	private $ds_ewpcf7_settings_page;

	private $ds_ewpcf7_settings = array();

	private $ds_ewpcf7_main_settings = array();

	// Create the object
	public function __construct() {

		// create the various menu pages 
		add_action( 'admin_menu', array($this, 'ds_ewpcf7_create_admin_page'));
		
		// Register the menu
		add_action( 'admin_menu', array($this, 'ds_ewpcf7_admin_menu' ));

		// add admin scripts
		add_action( 'admin_enqueue_scripts', array($this,  'ds_ewpcf7_admin_scripts' ));

		// make sure CF7 is active
		add_action( 'admin_notices', array($this, 'ds_ewpcf7_admin_notices' ));

		// Add settings & support links
		add_filter( 'plugin_action_links', array($this,'ds_ewpcf7_add_action_plugin'), 10, 5 );

		// check if we need to do any updates after plugin update
		add_action( 'upgrader_process_complete', array($this,'ds_ewpcf7_wp_upgrade_complete'), 10, 2);

	} // end public function __construct()

	public function ds_ewpcf7_create_admin_page() {
		// set the settings api options
		$ds_api_settings = array(
			'json_file' => plugin_dir_path( __FILE__ ) . '/contact-form-7-supercharger.json',
			'register_settings' => true, 
			'views_dir' => plugin_dir_path( __FILE__ ) . '/admin/views'
		);

		// Create the settings object
		$this->ds_ewpcf7_settings_page = new DSWPSettingsAPI\SettingsBuilder($ds_api_settings);

		// Get the current settings
		$this->ds_ewpcf7_settings = $this->ds_ewpcf7_settings_page->get_current_settings();

		// Get the plugin options
		$this->ds_ewpcf7_main_settings = $this->ds_ewpcf7_settings_page->get_main_settings();
	} // end function ds_ewpcf7_create_admin_page

	// Adds admin menu under the Sections section in the Dashboard
	public function ds_ewpcf7_admin_menu() {

		$this->ds_ewpcf7_plugin_hook = add_submenu_page(
				'wpcf7',
				__('Dusty Sun CF7 SUPERCHARGER', 'ds_ewpcf7'),
				__('Dusty Sun CF7 SUPERCHARGER', 'ds_ewpcf7'),
				'manage_options',
				'contact-form-7-supercharger',
				array($this, 'ds_ewpcf7_menu_options'));

	} // end public function ds_ewpcf7_admin_menu()

	public function ds_ewpcf7_admin_scripts( $hook ) {
		if($hook == $this->ds_ewpcf7_plugin_hook) {
			wp_enqueue_style('ds-ewpcf7-admin', plugins_url('/css/contact-form-7-supercharger-admin.css', __FILE__));
		}
	} // end public function ds_ewpcf7_admin_scripts
	
	// Admin notice
	public function ds_ewpcf7_admin_notices() {
	
		if( get_transient( 'ds_ewpcf7_updated' ) ) {
			echo '<div class="notice notice-success"><p>' . __( 'Thanks for updating Contact Form 7 SUPERCHARGER!', 'ds_ewpcf7' ) . '</p></div>';
			delete_transient( 'ds_ewpcf7_updated' );
		}
		// check if we need to upgrade any settings
		$this->ds_ewpcf7_upgrade_process();

		if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
				echo '<div class="error"><p>You have activated the <strong><a href="' . $this->ds_ewpcf7_main_settings['plugin_uri']  . '">' .  $this->ds_ewpcf7_main_settings['name'] .  '</a></strong> plugin, but you also need to install and activate <a href="plugin-install.php?tab=search&s=contact+form+7"><strong>Contact Form 7</strong></a>.</p></div>';
		}
	} // end function ds_ewpcf7_admin_notices

	// Create the actual options page
	public function ds_ewpcf7_menu_options() {
		$ds_ewpcf7_settings_title = $this->ds_ewpcf7_main_settings['name'];

		// Create the main page HTML
		$this->ds_ewpcf7_settings_page->build_settings_panel($ds_ewpcf7_settings_title);
	} // end function

	//function to add settings links to plugins area
	public function ds_ewpcf7_add_action_plugin( $actions, $plugin_file )
	{
		$plugin = plugin_basename(__DIR__) . '/contact-form-7-supercharger.php';
		if ($plugin == $plugin_file) {

			$site_link = array('support' => '<a href="' . $this->ds_ewpcf7_main_settings['plugin_uri'] . '" target="_blank">' . __('Support', $this->ds_ewpcf7_main_settings['text_domain']) . '</a>');
			$actions = array_merge($site_link, $actions);

			if ( is_plugin_active( $plugin ) ) {
				$settings = array('settings' => '<a href="admin.php?page=' . $this->ds_ewpcf7_main_settings['page_slug'] . '">' . __('Settings', $this->ds_ewpcf7_main_settings['text_domain']) . '</a>');
				$actions = array_merge($settings, $actions);
			} //end if is_plugin_active			

		}
		return $actions;

	} // end function ds_ewpcf7_add_action_plugin

	public function ds_ewpcf7_upgrade_process(){

		$update_db_flag = false;

		// Try the older version first
		$db_plugin_settings = get_option('ds_ewpcf7_plugin_settings');
		if($db_plugin_settings != '') {
			// move the settings from the older key and delete it
			delete_option('ds_ewpcf7_plugin_settings');
			update_option('ds_ewpcf7_main_settings', $db_plugin_settings);
		} else {
			$db_plugin_settings = get_option('ds_ewpcf7_main_settings');
		} // end if

		if($db_plugin_settings['version'] < '1.3') {

			// Remove the daily_news_check cron event 
			$timestamp = wp_next_scheduled ( $this->ds_ewpcf7_main_settings['item_slug'] . '_daily_news_check' );
			wp_unschedule_event($timestamp, $this->ds_ewpcf7_main_settings['item_slug'] . '_daily_news_check');

			// get the old license key option
			$ds_ewpcf7_update_settings = get_option('ds_ewpcf7_update_settings_options', true);
			if(isset($ds_ewpcf7_update_settings['ds_ewpcf7_update_email']) && $ds_ewpcf7_update_settings['ds_ewpcf7_update_email'] != '' )
			{
				update_option($this->ds_ewpcf7_main_settings['item_slug'] . '_wpla_license_email', $ds_ewpcf7_update_settings['ds_ewpcf7_update_email']);
			} // end if

			if(isset($ds_ewpcf7_update_settings['ds_ewpcf7_update_serialnumber']) && $ds_ewpcf7_update_settings['ds_ewpcf7_update_serialnumber'] != '' )
			{
				update_option($this->ds_ewpcf7_main_settings['item_slug'] . '_wpla_license_key', $ds_ewpcf7_update_settings['ds_ewpcf7_update_serialnumber']);
			} // end if

			// transfer the daily news check to daily license check
			$ds_ewpcf7_daily_news_check =  get_option($this->ds_ewpcf7_main_settings['item_slug'] . '_daily_news_check', true);

			update_option($this->ds_ewpcf7_main_settings['item_slug'] . '_daily_license_check', $ds_ewpcf7_daily_news_check);

			$update_db_flag = true;
		} // end if < 1.3

		if($db_plugin_settings['version'] != $this->ds_ewpcf7_main_settings['version']) {
			$update_db_flag = true;
		} // end if 

		if($update_db_flag) {
			//update the version info stored in the DB
			$this->ds_ewpcf7_settings_page->wl('Updating Dusty Sun CF7 SUPERCHARGER settings in DB...');
			$this->ds_ewpcf7_settings_page->set_main_settings(true);
		} // end if($update_db_flag) 

   } // end function ds_ewpcf7_upgrade_process

   public function ds_ewpcf7_wp_upgrade_complete( $upgrader_object, $options ) {
	   $current_plugin_path_name = plugin_basename(__DIR__) . '/contact-form-7-supercharger.php';
	   if ($options['action'] == 'update' && $options['type'] == 'plugin' ){
	    	foreach($options['plugins'] as $each_plugin){
				if ($each_plugin == $current_plugin_path_name) {
					set_transient('ds_ewpcf7_updated', 1);
				} // end if ($each_plugin == $current_plugin_path_name)
			} // end foreach($options['plugins'] as $each_plugin)
		} // end if ($options['action'] == 'update' && $options['type'] == 'plugin' )
   } // end function ds_ewpcf7_wp_upgrade_complete
   
} // end class Enhanced_Contact_Form_7_Settings
if( is_admin() )
    $ds_ewpcf7_settings_page = new Enhanced_Contact_Form_7_Settings();
