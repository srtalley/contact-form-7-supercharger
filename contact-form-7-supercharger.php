<?php
/*
Plugin Name: Contact Form 7 SUPERCHARGER
Description: Adds great features to Contact Form 7 such as iOS-style hover labels, UTM code tracking, redirect page options, modern form styling and a customizable lightbox for error and success messages.
Author: Dusty Sun
Author URI: http://dustysun.com
Plugin URI: https://dustysun.com/contact-form-7-supercharger/
Version: 1.4.2
Text Domain: ds_ewpcf7
License: GPLv2
*/

namespace DustySun\CF7_Supercharger;
use \DustySun\WP_Settings_API\v2 as DSWPSettingsAPI;
use \DustySun\WP_License_Agent\Client\v1_5 as WPLA;

//Include the admin panel page
require_once( dirname( __FILE__ ) . '/lib/dustysun-wp-settings-api/ds_wp_settings_api.php');

//Admin functions
require_once( dirname( __FILE__ ) . '/contact-form-7-supercharger-admin.php');

//Functions to redirect forms
require_once( dirname( __FILE__ ) . '/modules/redirectpage.php');

//Functions for UTM tracking
require_once( dirname( __FILE__ ) . '/modules/utm.php');

//Add update checker
require_once( dirname( __FILE__ ) . '/lib/wp-license-agent-client/wp-license-agent.php');

class Enhanced_Contact_Form_7 {

  private $ds_ewpcf7_json_file;
  private $ds_ewpcf7_settings_obj;
  private $current_settings;
  private $main_settings;
  private $redirectpage;
  private $utm;

  public function __construct() {

    // construct tag generators
    $this->utm = new UTM_Module();
    $this->redirectpage = new RedirectPage_Module();

    add_action('plugins_loaded', array($this, 'ds_ewpcf7_build_update_checker') );
    
    $ds_ewpcf7_keep_processing = true;

    // get the settings
    $this->ds_ewpcf7_get_current_settings();

    // check if the license is valid
    $ds_ewpcf7_plugin_slug = $this->main_settings['item_slug'];

    $license_info = get_option($ds_ewpcf7_plugin_slug . '_daily_license_check', true);

    //check if the license is valid and if we are disabling functions
    if(isset($license_info->valid) && !$license_info->valid && $license_info->disable_functionality) {
      $ds_ewpcf7_keep_processing = false;
    } // end if

    if($ds_ewpcf7_keep_processing) {
      // Register scripts
      add_action( 'wp_enqueue_scripts', array( $this, 'ds_ewpcf7_register_styles_scripts' ) );

      // add a class to our forms
      add_filter('wpcf7_form_class_attr', array($this,'ds_ewpcf7_add_form_class' ));

      // check the form elements and add hover labels
      add_filter('wpcf7_form_elements', array($this, 'ds_ewpcf7_modify_form_elements'), 9999);

      // modify the output response and add our lightbox
      if($this->current_settings['ds_ewpcf7_form_style_options']['lightbox_enabled'] == "yes"):
        add_filter( 'wpcf7_form_response_output', array($this, 'ds_ewpcf7_modify_form_response_output'));
      endif;

      // if the UTM is supposed to be in every post, turn it on with this filter
      if($this->current_settings['ds_ewpcf7_utm_tracking_options']['utm_all_forms'] == "yes"):
        // modify the email that gets sent
        add_action('wpcf7_before_send_mail', array($this, 'ds_ewpcf7_modify_mail'));
      endif;

    } // end if($ds_ewpcf7_keep_processing)
    // set the default settings
		register_activation_hook( __FILE__, array($this, 'ds_ewpcf7_default_settings' ));
  } // end public function __construct

public function ds_ewpcf7_get_current_settings(){
    // set the settings api options
    $ds_api_settings = array(
      'json_file' => plugin_dir_path( __FILE__ ) . '/contact-form-7-supercharger.json'
    );
    $this->ds_ewpcf7_settings_obj = new DSWPSettingsAPI\SettingsBuilder($ds_api_settings);

    // get the settings
    $this->current_settings = $this->ds_ewpcf7_settings_obj->get_current_settings();
    $this->main_settings = $this->ds_ewpcf7_settings_obj->get_main_settings();
  } // end function ds_ewpcf7_get_current_settings

  public function ds_ewpcf7_default_settings() {
    // Must be called after the settings_obj is set
    if(!$this->ds_ewpcf7_settings_obj || $this->ds_ewpcf7_settings_obj == '') {
      $this->ds_ewpcf7_get_current_settings();
    } // end if
    $this->ds_ewpcf7_settings_obj->set_current_settings(true);
    $this->ds_ewpcf7_settings_obj->set_main_settings(true);
  } // end function ds_ewpcf7_default_settings()

  public function ds_ewpcf7_register_styles_scripts() {

    wp_enqueue_script('contact-form-7-supercharger-main', plugins_url('js/contact-form-7-supercharger.js', __FILE__), '', '', true);

    wp_enqueue_script('contact-form-7-supercharger-utm', plugins_url('js/contact-form-7-supercharger-utm.js', __FILE__), '', '', true);

    // sync with latest version for DSWPSettingsAPI\SettingsBuilder
    $this->ds_ewpcf7_conditional_style_enqueue('fontawesome', 'https://use.fontawesome.com/releases/v5.1.1/css/all.css', '5.1.1' );

    wp_enqueue_style('ds-ewpcf7-styles', plugins_url('/css/contact-form-7-supercharger.css', __FILE__));

    $ds_custom_form_style = $this->current_settings['ds_ewpcf7_form_style_options']['form_style'];
    if($ds_custom_form_style == 'yes') :
      wp_enqueue_style('ds-ewpcf7-styles-custom-form', plugins_url('/css/contact-form-7-supercharger-custom-form.css', __FILE__));
    endif;

     // get the variables
      $ds_hover_label_bg_color = $this->current_settings['ds_ewpcf7_form_style_options']['hover_label_bg_color'];
      $ds_hover_label_text_color = $this->current_settings['ds_ewpcf7_form_style_options']['hover_label_text_color'];

      $new_custom_css = '
        .ds-hover-label-focus label {
          background-color: ' . $ds_hover_label_bg_color . ';
          color: ' . $ds_hover_label_text_color . ';
        }
        .ds-hover-label-focus label:after {
          border-top-color: ' . $ds_hover_label_bg_color . ';

        input.wpcf7-date:focus, input.wpcf7-text:focus, .wpcf7-textarea:focus, .wpcf7-captchar:focus, span.ds-wpcf7-select.wpcf7-form-control-wrap:hover {
          border-color: ' . $ds_hover_label_bg_color . ' !important;
        }
      ';

      wp_add_inline_style('ds-ewpcf7-styles', $new_custom_css);

  } //end function ds_ewpcf7_modify_load_css

  public function ds_ewpcf7_conditional_style_enqueue($ds_handle='', $ds_path = '', $ds_version = '', $ds_dependencies = 'array()', $ds_media = 'all'){
    if(wp_style_is($ds_handle, $ds_list = 'enqueued')){
      return;
    } else {
      wp_enqueue_style($ds_handle, $ds_path, $ds_dependencies, $ds_version, $ds_media);
    }
  } // end public function ds_conditional_enqueue

  // modify the form response
  public function ds_ewpcf7_add_form_class($class) {
    $newclasses = $class . ' ds-ewpcf7';

    if($this->current_settings['ds_ewpcf7_form_style_options']['lightbox_enabled'] == "yes") { 
      $newclasses = $newclasses . ' ds-ewpcf7-lightbox-enabled';
    } // end if
    return $newclasses;
  } // end ds_ewpcf7_add_form_class

  function ds_ewpcf7_modify_form_response_output($content) {

    $ds_ewpcf7_form_settings = $this->current_settings['ds_ewpcf7_form_style_options'];

    $ds_ewpcf7_output_dom = new \domDocument;

    // load the html into the object ***/
    $ds_ewpcf7_output_dom->strictErrorChecking = FALSE;
    $ds_ewpcf7_output_dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES',  'UTF-8'));
    libxml_use_internal_errors(false);

    // discard white space
    $ds_ewpcf7_output_dom->preserveWhiteSpace = false;

    // Get the icons from the settings
    $ds_ewpcf7_error_icon_picker_option = $ds_ewpcf7_form_settings['error_icon'];
    $ds_ewpcf7_success_icon_picker_option = $ds_ewpcf7_form_settings['success_icon'];

    // get the original WPCF7 div & HTML
    $ds_ewpcf7_output_original_html_nodes = $ds_ewpcf7_output_dom->getElementsByTagName('div');
    foreach($ds_ewpcf7_output_original_html_nodes as $ds_ewpcf7_output_original_html_node) {
      if(strstr($ds_ewpcf7_output_original_html_node->getAttribute('class'), 'wpcf7-response-output')) {

        $ds_ewpcf7_output_node_html = $ds_ewpcf7_output_original_html_node->ownerDocument->saveXML($ds_ewpcf7_output_original_html_node);
        // create a lightbox wrapper
        $ds_ewpcf7_output_wrapped_html = $ds_ewpcf7_output_dom->createElement('div');
        $ds_ewpcf7_output_wrapped_html_class = $ds_ewpcf7_output_dom->createAttribute('class');
        $ds_ewpcf7_output_wrapped_html_class->value = 'ewpcf7-modal';
        $ds_ewpcf7_output_wrapped_html->appendChild($ds_ewpcf7_output_wrapped_html_class);

        // get the literal HTML of the containing wrapper (div class)
        $ds_ewpcf7_output_inner_html = '
            <div class="ewpcf7-modal-box">
              <div class="ewpcf7-close"><i class="fa fa-times" aria-hidden="true"></i></div><!--ewpcf7-close-->
              <div class="ewpcf7-icon ewpcf7-icon-notice"><i class="fa ' . $ds_ewpcf7_error_icon_picker_option . '" aria-hidden="true"></i></div>
              <div class="ewpcf7-icon ewpcf7-icon-success"><i class="fa ' . $ds_ewpcf7_success_icon_picker_option . '" aria-hidden="true"></i></div>
              <div class="ewpcf7-modal-inner">
                <div class="ewpcf7-modal-content">' . $ds_ewpcf7_output_node_html . '</div>
                <div class="ewpcf7-modal-additional-messages"></div>
              </div> <!--ewpcf7-modal-inner-->
            </div> <!--ewpcf7-modal-box-->
            <div class="ewpcf7-modal-background"></div> <!--ewpcf7-modal-background-->
        ';

        // make a fragment and add it
        $ds_ewpcf7_output_inner_html_fragment = $ds_ewpcf7_output_dom->createDocumentFragment();
        $ds_ewpcf7_output_inner_html_fragment->appendXML($ds_ewpcf7_output_inner_html);
        $ds_ewpcf7_output_wrapped_html->appendChild($ds_ewpcf7_output_inner_html_fragment);

        // modify the content and add our new lightbox
        $ds_ewpcf7_output_original_html_node->parentNode->parentNode->replaceChild($ds_ewpcf7_output_wrapped_html, $ds_ewpcf7_output_original_html_node->parentNode);



        

      }// end if(strstr($tag->getAttribute('class'), 'wpcf7-response-output'))
    } // end foreach($ds_ewpcf7_output_original_html_nodes as $ds_ewpcf7_output_original_html_node)

    // remove any html tags around the item
    // $trim_off_front = strpos($ds_ewpcf7_output_dom->saveHTML(),'<html>') + 6;
    // $trim_off_end = (strrpos($ds_ewpcf7_output_dom->saveHTML(),'</html>')) - strlen($ds_ewpcf7_output_dom->saveHTML());

    // $trimmed_result = substr($ds_ewpcf7_output_dom->saveHTML(), $trim_off_front, $trim_off_end);

    // new method to remove any html tags around the item
    $trimmed_result = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $ds_ewpcf7_output_dom->saveHTML()));

    // return our modified HTML
    return $trimmed_result;

  } // end function ds_ewpcf7_modify_form_response_output($content)


  public function ds_ewpcf7_modify_form_elements($content) {

    // https://davidwalsh.name/domdocument
    libxml_use_internal_errors(true);

    $ds_ewpcf7_dom = new \domDocument('1.0', 'utf-8');
    // load the html into the object ***/
    $ds_ewpcf7_dom->strictErrorChecking = FALSE;
    $ds_ewpcf7_dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES',  'UTF-8'));
    libxml_use_internal_errors(false);
    // discard white space
    $ds_ewpcf7_dom->preserveWhiteSpace = false;

    // Get the inputs and textareas
    $ds_ewpcf7_inputs = $ds_ewpcf7_dom->getElementsByTagName('input');
    $ds_ewpcf7_textareas = $ds_ewpcf7_dom->getElementsByTagName('textarea');

    // Set the selects if the form styling option is turned on
    $ds_ewpcf7_form_style = $this->current_settings['ds_ewpcf7_form_style_options']['form_style'];
    $ds_ewpcf7_selects = array();
    if($ds_ewpcf7_form_style == 'yes') {
      $ds_ewpcf7_selects = $ds_ewpcf7_dom->getElementsByTagName('select');
    }

    // create an array for our combined inputs
    $ds_ewpcf7_tags = [];

    // combine the inputs and textareas and selects into the array
    foreach ($ds_ewpcf7_inputs as $ds_ewpcf7_input):
      $ds_ewpcf7_tags[] = $ds_ewpcf7_input;
    endforeach; // foreach ($ds_ewpcf7_inputs as $ds_ewpcf7_input):

    foreach ($ds_ewpcf7_textareas as $ds_ewpcf7_textarea):
      $ds_ewpcf7_tags[] = $ds_ewpcf7_textarea;
    endforeach; // foreach ($ds_ewpcf7_textareas as $ds_ewpcf7_textarea):

    foreach ($ds_ewpcf7_selects as $ds_ewpcf7_select):
      $ds_ewpcf7_tags[] = $ds_ewpcf7_select;
    endforeach; // foreach ($ds_ewpcf7_selects as $ds_ewpcf7_select):

    // Go through each input or text array and add our hover label
    foreach ($ds_ewpcf7_tags as $ds_ewpcf7_tag):

      // match either wpcf7-text or wpcf7-textarea classes
      if(preg_match_all('~\b(wpcf7-text|wpcf7-textarea)\b~', $ds_ewpcf7_tag->getAttribute('class'))) {

        // see if there's a placeholder in the element. If not, skip.
        if($ds_ewpcf7_tag->getAttribute('placeholder') != '' && $ds_ewpcf7_tag->getAttribute('placeholder') != null) {

          $ds_ewpcf7_input_html = $ds_ewpcf7_tag->parentNode->ownerDocument->saveXML($ds_ewpcf7_tag->parentNode);

          // wrap the html
          // create the div
          $ds_ewpcf7_wrapped_html = $ds_ewpcf7_dom->createElement('span');
          $ds_ewpcf7_wrapped_html_class = $ds_ewpcf7_dom->createAttribute('class');
          $ds_ewpcf7_wrapped_html_class->value = 'ds-hover-label ds-show-placeholder';
          $ds_ewpcf7_wrapped_html->appendChild($ds_ewpcf7_wrapped_html_class);

          // add the label with the placeholder text
          $ds_ewpcf7_label = $ds_ewpcf7_dom->createElement('label', $ds_ewpcf7_tag->getAttribute('placeholder'));
          $ds_ewpcf7_label_class = $ds_ewpcf7_dom->createAttribute('class');
          $ds_ewpcf7_label_class->value = 'ds-label';
          $ds_ewpcf7_label->appendChild($ds_ewpcf7_label_class);
          $ds_ewpcf7_wrapped_html->appendChild($ds_ewpcf7_label);

          // create the document fragment so we can import the earlier HTML directly
          $ds_ewpcf7_input_html_fragment = $ds_ewpcf7_dom->createDocumentFragment();
          $ds_ewpcf7_input_html_fragment->appendXML($ds_ewpcf7_input_html);
          $ds_ewpcf7_wrapped_html->appendChild($ds_ewpcf7_input_html_fragment);

          $ds_ewpcf7_tag->parentNode->parentNode->replaceChild($ds_ewpcf7_wrapped_html, $ds_ewpcf7_tag->parentNode);

        } // if($ds_ewpcf7_tag->getAttribute('placeholder') != '' && $ds_ewpcf7_tag->getAttribute('placeholder') != null)

      } // end if(preg_match_all('~\b(wpcf7-text|wpcf7-textarea)\b~', $ds_ewpcf7_tag->getAttribute('class')))
      else if(preg_match('~\bwpcf7-select\b~', $ds_ewpcf7_tag->getAttribute('class'))) {
        // check if this is a select.

        // add a class to the parent wrapper.
        $ds_ewpcf7_tag_parent_classes = $ds_ewpcf7_tag->parentNode->getAttribute('class');
        $ds_ewpcf7_tag->parentNode->setAttribute('class', $ds_ewpcf7_tag_parent_classes . ' ds-wpcf7-select');

      } // end else if(preg_match('~\bwpcf7-select\b~', $ds_ewpcf7_tag->getAttribute('class')))
      else if(preg_match('~\bwpcf7-submit\b~', $ds_ewpcf7_tag->getAttribute('class'))) {
          // check if this is the submit button input. If so, set it aside for later processing
          $ds_ewpcf7_submit = $ds_ewpcf7_tag;
      } // end else if(preg_match('~\bwpcf7-submit\b~', $ds_ewpcf7_tag->getAttribute('class')))
    endforeach; // end foreach ($ds_ewpcf7_tags as $ds_ewpcf7_tag)

    // See if the option is turned on to modify the submit button. If so, and if a submit button was found, modify it.
    if(isset($ds_ewpcf7_submit)) {

      // Get the icons from the settings
      $ds_ewpcf7_modify_submit_button = $this->current_settings['ds_ewpcf7_form_style_options']['submit_button_type'];

      if ($ds_ewpcf7_modify_submit_button == 'button'){
        // create a div that wraps the button
        $ds_ewpcf7_submit_wrapped_html = $ds_ewpcf7_dom->createElement('span');
        $ds_ewpcf7_submit_wrapped_html_class = $ds_ewpcf7_dom->createAttribute('class');
        $ds_ewpcf7_submit_wrapped_html_class->value = 'ewpcf7-submit';
        $ds_ewpcf7_submit_wrapped_html->appendChild($ds_ewpcf7_submit_wrapped_html_class);

        // get the class of the existing is_object
        $ds_ewpcf7_submit_classes = $ds_ewpcf7_submit->getAttribute('class');
        // get the value of the existing object
        $ds_ewpcf7_submit_value = $ds_ewpcf7_submit->getAttribute('value');

        $ds_ewpcf7_submit_button = $ds_ewpcf7_dom->createElement('button', $ds_ewpcf7_submit_value);
        // create the class
        $ds_ewpcf7_submit_button_class = $ds_ewpcf7_dom->createAttribute('class');

        // check if the Divi theme is active and add more classes
        $ds_ewpcf7_active_theme = wp_get_theme();
        if($ds_ewpcf7_active_theme->name == 'Divi' || $ds_ewpcf7_active_theme->parent_theme == 'Divi') {
          $ds_ewpcf7_submit_classes .= ' et_pb_contact_submit et_pb_button ';
        } // end if $ds_ewpcf7_active_theme->name == 'Divi'
        $ds_ewpcf7_submit_button_class->value = $ds_ewpcf7_submit_classes;
        $ds_ewpcf7_submit_button->appendChild($ds_ewpcf7_submit_button_class);
        // set the type
        $ds_ewpcf7_submit_button_type = $ds_ewpcf7_dom->createAttribute('type');
        $ds_ewpcf7_submit_button_type->value = 'submit';
        $ds_ewpcf7_submit_button->appendChild($ds_ewpcf7_submit_button_type);

        // add it to the div
        $ds_ewpcf7_submit_wrapped_html->appendChild($ds_ewpcf7_submit_button);

        // replaced the existing eleemnt with our new div and button
        $ds_ewpcf7_submit->parentNode->replaceChild($ds_ewpcf7_submit_wrapped_html, $ds_ewpcf7_submit);
      } // end modify submit

    } // end if($ds_ewpcf7_submit)

    if($this->current_settings['ds_ewpcf7_utm_tracking_options']['utm_all_forms'] == "yes"):
      $utm_module = new UTM_Module();

      $utm_field_html = $utm_module->ds_utmz_create_hidden_fields();

      // see if there is html we should add 
      if($utm_field_html != '' && $utm_field_html != null) {
        // create a hidden field wrapper
        $ds_ewpcf7_utm_wrapped_html = $ds_ewpcf7_dom->createElement('div');
        $ds_ewpcf7_utm_wrapped_html_class = $ds_ewpcf7_dom->createAttribute('class');
        $ds_ewpcf7_utm_wrapped_html_class->value = 'wpcf7-utm';
        $ds_ewpcf7_utm_wrapped_html->appendChild($ds_ewpcf7_utm_wrapped_html_class);
        $ds_ewpcf7_utm_inner_html_fragment = $ds_ewpcf7_dom->createDocumentFragment();
        $ds_ewpcf7_utm_inner_html_fragment->appendXML($utm_field_html);
        $ds_ewpcf7_utm_wrapped_html->appendChild($ds_ewpcf7_utm_inner_html_fragment);

        // append it to the form
        $ds_ewpcf7_dom->appendChild($ds_ewpcf7_utm_wrapped_html);

      } // end if($utm_field_html != '' && $utm_field_html != null)
    endif;

    $ds_ewpcf7_modified_content = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $ds_ewpcf7_dom->saveHTML()));

    return $ds_ewpcf7_modified_content;

  } // end function ds_ewpcf7_modify_form_elements

  public function ds_ewpcf7_modify_mail($wpcf7_form){
    $current_submission = \WPCF7_Submission::get_instance();

    if($current_submission){

      $utm_html = '';
      // Get our UTM POST values
      if(isset($_POST['utm_source']) && !empty($_POST['utm_source'])) {
        $utm_values = array();
        $utm_values['utm_source'] = $_POST['utm_source'];
        $utm_values['utm_medium'] = isset($_POST['utm_medium']) && !empty($_POST['utm_medium']) ? $_POST['utm_medium'] : '';
        $utm_values['utm_campaign'] = isset($_POST['utm_campaign']) && !empty($_POST['utm_campaign']) ? $_POST['utm_campaign'] : '';
        $utm_values['utm_term'] = isset($_POST['utm_term']) && !empty($_POST['utm_term']) ? $_POST['utm_term'] : '';
        $utm_values['utm_content'] = isset($_POST['utm_content']) && !empty($_POST['utm_content']) ? $_POST['utm_content'] : '';
        $utm_values['utm_landing_page'] = isset($_POST['utm_landing_page']) && !empty($_POST['utm_landing_page']) ? $_POST['utm_landing_page'] : '';
        $utm_values['utm_landing_page_date'] = isset($_POST['utm_landing_page_date']) && !empty($_POST['utm_landing_page_date']) ? $_POST['utm_landing_page_date'] : '';
        // build our HTML
        $utm_html  = '<style type="text/css">';
        $utm_html .= 'table#utm-values tr td:first-child { min-width: 150px; padding-right: 10px; }';
        $utm_html .= 'table#utm-landing-page .utm-url { width: 100%; word-break:break-all; }';
        $utm_html .= '</style>';
        $utm_html .= '<h3 style="margin-left:4px;">UTM Values</h3>';
        $utm_html .= '<table id="utm-values">';
        $utm_html .= '<tr><td><strong>UTM Source</strong></td><td>' . $utm_values['utm_source'] . '</td></tr>';
        $utm_html .= '<tr><td><strong>UTM Medium</strong></td><td>' . $utm_values['utm_medium'] . '</td></tr>';
        $utm_html .= '<tr><td><strong>UTM Campaign</strong></td><td>' . $utm_values['utm_campaign'] . '</td></tr>';
        $utm_html .= '<tr><td><strong>UTM Term</strong></td><td>' . $utm_values['utm_term'] . '</td></tr>';
        $utm_html .= '<tr><td><strong>UTM Content</strong></td><td>' . $utm_values['utm_content'] . '</td></tr>';
        $utm_html .= '<tr><td><strong>Landing Page Date &amp; Time</strong></td><td>' . $utm_values['utm_landing_page_date'] . '</td></tr>';
        $utm_html .= '</table>';
        $utm_html .= '<table id="utm-landing-page">';
        $utm_html .= '<tr><td><strong>Landing Page URL</strong></td></tr>';
        $utm_html .= '<tr><td class="utm-url">' . $utm_values['utm_landing_page'] . '</td></tr>';
        $utm_html .= '</table>';

      } // end if(isset($_POST['utm_source']) && !empty($_POST['utm_source']))

      $current_mail = $wpcf7_form->prop('mail');
      // turn on html email
      $current_mail['use_html'] = 1;
      $current_mail['body'] .= $utm_html;
      $wpcf7_form->set_properties(array(
        'mail' => $current_mail
      ));
    } // end if($current_submission)
    return $wpcf7_form;
  } // end function ds_ewpcf7_modify_mail

  public function ds_ewpcf7_build_update_checker() {
    $this->ds_ewpcf7_get_current_settings();
    $settings = array(
      'update_url' => $this->main_settings['wpla_update_url'],
      'update_slug' => $this->main_settings['item_slug'],
      'main_file' => __FILE__,
      'news_widget' => true,
      'puc_errors' => false
    );
    $this->update_checker = new WPLA\Licensing_Agent($settings);

  } // end function ds_ewpcf7_build_update_checker

} // end class Enhanced_Contact_Form_7

$ds_ewpcf7 = new Enhanced_Contact_Form_7();
