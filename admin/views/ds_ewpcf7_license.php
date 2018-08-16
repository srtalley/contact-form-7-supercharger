
<?php 
$update_slug = $this->main_settings['item_slug'];
use \DustySun\WP_License_Agent\Client\v1_5 as WPLA;
$license_panel = new WPLA\License_Panel($update_slug);
echo $license_panel->show_license_panel();
?>