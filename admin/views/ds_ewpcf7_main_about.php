<?php

//vars
$ds_version = $this->main_settings['version'];
$ds_author = $this->main_settings['author'];
$ds_author_uri = $this->main_settings['author_uri'];
$ds_plugin_name = $this->main_settings['name'];
$ds_plugin_uri = $this->main_settings['item_uri'];
$ds_plugin_email = $this->main_settings['support_email'];

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
