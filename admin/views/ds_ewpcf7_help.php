<?php
//vars
$ds_support_uri = $this->main_settings['support_uri'];
$ds_support_email = $this->main_settings['support_email'];
?>

<div>
  <p><a href="#utm-tracking">UTM Tracking</a><br>
  <a href="#hover-labels">iOS-Style Hover Labels</a><br>
  <a href="#redirectpages">Redirect Pages</a><br>
  <a href="#lightboxes">Success and Error Message Lightboxes</a><br>
  <a href="#form-styling">Modern Form CSS Styling</a><br>
  <a href="#button-element">HTML5 Submit Button Element</a></p>
  <h2 id="utm-tracking">UTM Tracking</h2>
  <p>Many people have asked for a way to track the UTM codes from your visitors. Would you like to know what the UTM codes were when a visitor arrived on your form? No problem!!!</p>
  <p>Depending on the method you choose (detailed below), if a visitor lands on your site with UTM codes, these will be sent along with the form. In some cases, a visitor may have landed on a different page on your site and then navigated to your contact form. So that you have the ultimate data at your fingertips, you’ll see the landing page as well since it may be different than your contact form. You’ll also see the date and time when they first landed on your site with the UTM codes.</p>

  <p>Even better? This is fully compatible with Flamingo and all fields are recorded in the Flamingo database entry.</p>

  <p>You have two options in order to track these unique codes:</p>
  <h4>1. Track UTM codes for all forms:</h4>
  <p>Most people will want this option – simply turn on the feature in the UTM Tracking tab of this plugin. All forms on your site will gather the UTM codes, if available.</p>
    <p><img src="<?php echo plugins_url('views/images/utm_tracking_plugin_options.jpg', dirname(__FILE__));?>"/></p>
<h4>2. Individual Form Tracking: </h4>
  <p>For various reasons, you may not want to get the UTM codes on all forms on your site. Instead of turning on the tracking for all forms, simply use the tag generator when editing a form in Contact Form 7 or manually add the&nbsp;<strong>[utm]&nbsp;</strong>tag to your form.</p>
    <p><img src="<?php echo plugins_url('views/images/utm_tracking_tag_generator_button.jpg', dirname(__FILE__));?>"/></p>

  <h4>Example Email:</h4>
  <p><img src="<?php echo plugins_url('views/images/utm_example_email.png', dirname(__FILE__));?>"/></p>
  <hr>
  <h2 id="hover-labels">iOS-Style Hover Labels</h2>
  <p><img src="<?php echo plugins_url('views/images/hover_label_example.png', dirname(__FILE__));?>"/></p>
  <p>Hover labels require you to set up a form with a placeholder inside the input or text field. For example, this field for a person’s name has the placeholder “First Name” which will be converted into a hover label when the field is clicked in:</p>
  <p><tt>[text your-first-name placeholder "First Name"]</tt></p>
  <p>So when you add a text, email, phone, text area, etc., just make sure to set the placeholder. If you do, it will be replaced with the iOS-style hovering label.</p>
  <p>On the General tab of this plugin, there are options where you can easily change the background and text colors of the labels that are shown.</p>
  <p><img src="<?php echo plugins_url('views/images/hover_label_options.png', dirname(__FILE__));?>"/></p>
  <hr>
  <h2 id="redirectpages">Redirect Pages</h2>
  <p>While the author of Contact Form 7 is absolutely correct that redirect pages are not needed in the majority of cases, sometimes you do want to show your visitor a different page. We’ve got you covered!</p>
  <p>Using a fully-compatible method (not the outdated on_sent_ok script), you can easily add a redirect page to each form.</p>
  <p>Simply edit your form and use the&nbsp;<strong>[redirectpage]</strong> tag generator button.</p>
  <p>You can also manually add a tag like this:</p>
  <pre>[redirectpage "https://yourredirectpage.com"]</pre>
  <h2 id="lightboxes">Success and Error Message Lightboxes</h2>
  <p>On the General tab of this plugin, you can turn the lightbox on or off. When on, the success and error messages that Contact Form 7 normally displays at the bottom of the form will be shown in a nicely-designed lightbox.</p>
  <p>You can change the icon that is shown for error or success messages on the General tab of this plugin.</p>
  <h2 id="form-styling">Modern Form CSS Styling</h2>
  <p>We’ve created form settings that we think really make your Contact Form 7 forms look nice. Have your own form styling? No problem – just turn ours off on the General tab.</p>
  <p>Otherwise, turn the form styling on to use our styles. Please let us know what you think!</p>
  <h2 id="button-element">HTML5 Submit Button Element</h2>
  <p>By default Contact Form 7 makes an input style submit button. Would you rather have an HTML5 button element? Easy! Just turn on the option on the General tab in this plugin.</p>
  <p>(Bonus: If you’re using the Divi theme, it will be styled as a Divi form button.)</p>


  <h4>Need help? <a href="<?php echo $ds_support_uri; ?>">Contact us!</a> You can also send us an email at <a href="mailto:<?php echo $ds_support_email; ?>"><?php echo $ds_support_email; ?></a></h4>

</div>
