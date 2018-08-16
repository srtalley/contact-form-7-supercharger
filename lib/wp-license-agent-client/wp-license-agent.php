<?php
/*
 * WP License Agent Update Checker Plugin & Theme Update Library
 *
 * Version 1.5.4
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

    use \DustySun\WP_License_Agent\Client\v1_5 as WPLA;
    $wpla_update_checker = new WPLA\Licensing_Agent($wpla_settings);

    ----------

    To show the license panel info inside your plugin anywhere, simply
    add the namespace and then call the show_license_panel function,
    but make sure you pass your plugin or theme's update slug to the 
    function. This should be the same update slug you used when creating
    the Licensing_Agent class as shown above.

    use \DustySun\WP_License_Agent\Client\v1_5 as WPLA;

    echo WPLA\License_Panel::show_license_panel('your-update-slug');
 */
namespace DustySun\WP_License_Agent\Client\v1_5;

// Load Required libraries
require_once( dirname( __FILE__ ) . '/inc/panel.php');
require_once( dirname( __FILE__ ) . '/inc/updater.php');

if(!class_exists('DustySun\WP_License_Agent\Client\v1_5\WPLA_Client_Factory')) { 
    class WPLA_Client_Factory {

          /**
   * @param string $filePath
   * @return string
   * Adapted from plugin-update-checker
   */
    static function get_updater_url($filePath) {
        
        $absolutePath = realpath(dirname(__FILE__) . '/' . ltrim($filePath, '/'));

        //Where is the library located inside the WordPress directory structure?
        $absolutePath = wp_normalize_path($absolutePath);
        $pluginDir = wp_normalize_path(WP_PLUGIN_DIR);
        $muPluginDir = wp_normalize_path(WPMU_PLUGIN_DIR);
        $themeDir = wp_normalize_path(get_theme_root());

        if ( (strpos($absolutePath, $pluginDir) === 0) || (strpos($absolutePath, $muPluginDir) === 0) ) {
        //It's part of a plugin.
        return plugins_url(basename($absolutePath), $absolutePath);
        } else if ( strpos($absolutePath, $themeDir) === 0 ) {
        //It's part of a theme.
        $relativePath = substr($absolutePath, strlen($themeDir) + 1);
        $template = substr($relativePath, 0, strpos($relativePath, '/'));
        $baseUrl = get_theme_root_uri($template);

        if ( !empty($baseUrl) && $relativePath ) {
            return $baseUrl . '/' . $relativePath;
        }
        } 
        return '';
    } // end function get_updater_url

    /**
     * @param string $directory always __DIR__
     */
    static function get_product_type($directory) {
        $current_directory = WPLA_Client_Factory::forward_slashes($directory);
        $plugins_directory = WPLA_Client_Factory::forward_slashes(WP_PLUGIN_DIR);
        $mu_plugins_directory = WPLA_Client_Factory::forward_slashes(WPMU_PLUGIN_DIR);
        $themes_directory = WPLA_Client_Factory::forward_slashes(get_theme_root());

        if ( strpos ( $current_directory, $plugins_directory ) !== FALSE ) {
            $location = 'plugin';

        } elseif ( strpos ( $current_directory, $mu_plugins_directory ) !== FALSE ) {
            $location = 'mu-plugin';

        } elseif ( strpos ( $current_directory, $themes_directory ) !== FALSE ) {
            // Script is in a theme, determine if parent or child
            $stylesheet_directory = WPLA_Client_Factory::forward_slashes(get_stylesheet_directory());

            if ( is_child_theme() && ( strpos ( $current_directory, $stylesheet_directory ) !== FALSE ) ) {
                $location = 'child-theme';
            } else {
                $location = 'theme';
            }

        } else {
            // not in a theme or plugin
            $location = FALSE;
        }
        return $location;
    } // end static function get_product_type

    /**
     * Handle Windows paths
    */
    static function forward_slashes($string) {
        return str_replace('\\', '/', $string);
    } // end function forward_slashes

    static function wl ( $log )  {
        if(defined('WP_LICENSE_AGENT_DEBUG')) {
          if ( true === WP_LICENSE_AGENT_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
              error_log( print_r( $log, true ) );
            } else {
              error_log( $log );
            }
          }
        }
    } // end function wl


}
}
    