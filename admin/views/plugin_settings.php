<!--Facebook-->
<!--https://developers.facebook.com/docs/plugins/share-button#-->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.11&appId=241973699583991&autoLogAppEvents=1';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>


<!--Twitter-->
<script>window.twttr = (function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0],
    t = window.twttr || {};
  if (d.getElementById(id)) return t;
  js = d.createElement(s);
  js.id = id;
  js.src = "https://platform.twitter.com/widgets.js";
  fjs.parentNode.insertBefore(js, fjs);

  t._e = [];
  t.ready = function(f) {
    t._e.push(f);
  };

  return t;
}(document, "script", "twitter-wjs"));</script>

<!--Google-->
<!--https://developers.google.com/+/web/share/-->
<script src="https://apis.google.com/js/platform.js" async defer></script>

<div class="ds-wp-settings-flex-columns">
  <div class="ds-wp-settings-two-thirds ds-wp-settings-space-between">
    <div>
					<p>This plugin adds the following features, which you can turn on or off, to Contact Form 7:</p>
            <ul style="list-style: disc; margin-left: 40px;">
            <li><strong>UTM Tracking:&nbsp;</strong>Want to track the UTM fields of visitors who land on your site and submit a form? Turn tracking on for all forms or just one form with the <strong>[utm]</strong> tag.</li>
            <li><strong>Hover labels:</strong>&nbsp;if you use a placeholder, it’s turned into an iOS style hovering label above the field while the user enters the data .</li>
            <li><strong>Enhanced error or success messages:</strong> The error and success messages you define in the Contact Form 7 settings are displayed in a nicely designed lightbox. You can even change the icons shown!</li>
            <li><strong>Loading animation:</strong> When a form is submitted, a spinning icon is shown over the form letting the user know that something is happening so that they don’t repeatedly try clicking the submit button.</li>
            <li><strong>Modern CSS Form Styling:&nbsp;</strong>We've added some modern updates to the Contact Form 7 form styles so they look a lot better "out of the box." You can easily turn this off if you want.</li>
            <li><strong>Redirect pages:</strong> Add a redirect page easily with the <strong>[redirectpage]</strong> tag. This uses fully supported methods in Contact Form 7 v5+.</li>
            </ul>

            <p>Need detailed help? No problem! Check the <a href="?page=<?php echo $this->plugin_settings['page_slug'];?>&amp;tab=ds_ewpcf7_help">help tab</a> below for step-by-step instructions.</p>

      </div>

  </div>
  <div class="ds-wp-settings-one-third ds-wp-settings-logo-display">
      <img src="<?php echo plugins_url('views/images/cf7_supercharger_logo_square_v2.png', dirname(__FILE__));?>">
  </div>

</div>

<div>
  <!-- Begin MailChimp Signup Form -->
  <link href="//cdn-images.mailchimp.com/embedcode/horizontal-slim-10_7.css" rel="stylesheet" type="text/css">
  <style type="text/css">
    #mc_embed_signup{background:#fff; clear:left; font:16px 'Open Sans',Arial,sans-serif; width:100%;}
    /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
       We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
  </style>
  <div id="mc_embed_signup">
  <form action="https://dustysun.us13.list-manage.com/subscribe/post?u=f9e097f181dd4927894fb3b6c&amp;id=aae905235f" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
      <div id="mc_embed_signup_scroll">
    <label for="mce-EMAIL">Sign up for updates on this plugin &amp; more!</label>
    <input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
      <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
      <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_f9e097f181dd4927894fb3b6c_aae905235f" tabindex="-1" value=""></div>
      <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
      </div>
  </form>
  </div>

  <!--End mc_embed_signup-->
<div class="ds-wp-settings-api-share-box" style="margin-bottom:10px;">
  <h4>Love this plugin? Please share with your friends! :)</h4>

  <!-- Your share button code -->
  <div class="fb-share-button" data-href="https://dustysun.com" data-layout="button" data-size="large"></div>

  <a class="twitter-share-button"
    href="https://twitter.com/intent/tweet?text=Check%20Out%20WooCommerce%20Coupons%20via%20Rest!&url=https://DustySun.com"
    data-size="large"></a>

  <!-- Place this tag where you want the share button to render. -->
  <div class="g-plus" data-action="share" data-height="24" data-href="https://dustysun.com"></div>

</div>

</div>
