<?php

//vars

$ds_wc_crr_plugin_settings = $this->plugin_settings;
$ds_version = $ds_wc_crr_plugin_settings['version'];
$ds_author = $ds_wc_crr_plugin_settings['author'];
$ds_author_uri = $ds_wc_crr_plugin_settings['author_uri'];
$ds_plugin_name = $ds_wc_crr_plugin_settings['plugin_name'];
$ds_plugin_uri = $ds_wc_crr_plugin_settings['plugin_uri'];
$ds_plugin_email = $ds_wc_crr_plugin_settings['support_email'];

?>

<p>We love Contact Form 7 and know you do, too! The possibilities are endless - we hope you enjoy the additions to this great plugin. Please <a href="mailto:<?php echo $ds_plugin_email;?>">let us know</a> if you have any feedback or suggestions.</p>

<div style="text-align:center;" class="ds-wp-settings-api-admin-flexcenter">
  <p><strong><?php echo $ds_plugin_name; ?></strong></p>
  <p>Version: <?php echo $ds_version; ?></p>
  <p>Author: <a href="<?php echo $ds_author_uri; ?>" target="_blank"><?php echo $ds_author; ?></a></p>
  <p>Plugin Homepage: <a href="<?php echo $ds_plugin_uri; ?>" target="_blank"><?php echo $ds_plugin_uri; ?></a></p>
</div>
<hr style="margin-top:25px;">
<div>
  <?php echo $this->get_reset_ajax_form();?>
</div>
