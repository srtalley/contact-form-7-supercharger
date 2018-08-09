<?php
/*
 * WP License Agent Update Checker Plugin & Theme Update Library
 *
 * Version 1.4.4
 *
 * https://dustysun.com
 *
 * Copyright 2018 Dusty Sun
 * Released under the GPLv2 license.
 *
 * USAGE:
 * require this file in your plugin or theme (and make sure the plugin-update-checker
 * directory is in the same directory as this file).
 * 
 * require_once( dirname( __FILE__ ) . '/lib/wp-license-agent-client/wp-license-agent.php');

 * Create a settings array with the following info:

    $wpla_settings = array(
        'update_url' => 'server URL goes here',
        'update_slug' => 'slug / name of directory of plugin or theme',
        'main_file' => __FILE__, // main file of plug or any file in the theme
        'news_widget' => true, // show plugin/theme news on the dashboard
        'puc_errors' => true // show update checker errors,
        'license_errors' = true // show a banner in the admin area if license errors exist,
        'development' => false // set to true if you have a development version of the udpate defined on your WPLA server
    );

    finally, instantiate this object from your plugin:

    use \DustySun\WP_License_Agent\Updater\v1_4 as WPLA;
    $wpla_update_checker = new WPLA\Licensing_Agent($wpla_settings);

    ----------

    To show the license panel info inside your plugin anywhere, simply
    add the namespace and then call the show_license_panel function,
    but make sure you pass your plugin or theme's update slug to the 
    function. This should be the same update slug you used when creating
    the Licensing_Agent class as shown above.

    use \DustySun\WP_License_Agent\Updater\v1_4 as WPLA;

    echo WPLA\License_Panel::show_license_panel('your-update-slug');
 */

// Load Required libraries

require_once( dirname( __FILE__ ) . '/classes/updater.php');

require_once( dirname( __FILE__ ) . '/classes/panel.php');