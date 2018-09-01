WP License Agent Client Library
================

This class adds the WP License Agent updater and license panel to your plugin or theme.

Usage
-----
Include the entire wp-license-agent-client folder in your theme or plugin, and then require the wp-license-agent.php file. 

For example, if you placed the folder inside a folder named "lib" in your plugin or theme, you would require the file like this:

```
require_once( dirname( __FILE__ ) . '/lib/wp-license-agent-client/wp-license-agent.php');
```

You must then include the namespace, which will be updated with the version number whenever the client library is updated with new features.

```
use \DustySun\WP_License_Agent\Client\v1_5 as WPLA;
```

Next, you need to create an array with the following info:
```
$wpla_settings = array(
    'update_url' => 'server URL goes here',
    'update_slug' => 'slug / name of directory of plugin or theme',
    'main_file' => __FILE__, // main file of plug or any file in the theme
    'news_widget' => true, // show plugin/theme news on the dashboard
    'puc_errors' => true // show update checker errors,
    'license_errors' = true // show a banner in the admin area if license errors exist,
    'development' => false // set to true if you have a development version of the udpate defined on your WPLA server
);
```
Finally, you'll create the actual WP License Agent client:
```
$wpla_update_checker = new WPLA\Licensing_Agent($wpla_settings);
```

Showing the License Panel
-------------------------

By default, plugins will have an "Update License" link shown beneath their name in the Plugins screen within WordPress. 

Themes will show the panel on the Appearance > Themes page.

You can also manually include the license panel anywhere in your plugin or theme settings. Simply add the namespace and call "show_license_panel," but make sure you pass your plugin or theme's update slug to the function. This should be the same update slug you used when creating the Licensing_Agent class as shown above.

```
use \DustySun\WP_License_Agent\Client\v1_5 as WPLA;
echo WPLA\License_Panel::show_license_panel('your-update-slug');
```

wp-config.php Options
---------------------

The following options may be defined in your site's wp-config.php:
```
define( 'WP_LICENSE_AGENT_DEBUG', true );
```
This option turns on some logging to the debug.log for your site if you have logging turned on.

```
define( 'WP_LICENSE_AGENT_DEVELOPMENT_VERSIONS', true );
```
 Define this on a site to receive the development version of an update package - this naturally requires that you have added a development package to the update package on your WP License Agent server. If you have this option turned on but there is no development version available, then the main branch packages will be used instead.

```
define( 'WP_LICENSE_AGENT_TEST_URL' , 'https://your_alternate_url');
```
This allows you to override the update URL set within your plugin or theme. 

Changelog
---------
#### 1.5.6 - 2018-08-30
* Began tracking changes in this file.
* The license check now runs when the plugin is first installed.