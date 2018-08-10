
<?php 
$update_slug = $this->get_plugin_options()['plugin_slug'];
use \DustySun\WP_License_Agent\Updater\v1_4 as WPLA;
echo WPLA\License_Panel::show_license_panel($update_slug);
?>