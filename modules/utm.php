<?php
/**
** A base module for [utm]
**/

/* form_tag handler */
namespace DustySun\CF7_Supercharger;

if(!class_exists('DustySun\CF7_Supercharger\UTM_Module'))  { class UTM_Module {

  // Create the object
  public function __construct($data = null) {
    add_action( 'wpcf7_init', array($this, 'wpcf7_add_form_tag_utm' ));

    add_action( 'wpcf7_admin_init', array($this, 'wpcf7_add_tag_generator_utm'), 110 );
  } //end __construct

  public function wpcf7_add_form_tag_utm() {
  	wpcf7_add_form_tag( 'utm', array($this, 'wpcf7_utm_form_tag_handler') );
  }

  public function wpcf7_utm_form_tag_handler( $tag ) {

  	$class = wpcf7_form_controls_class( $tag->type );

  	$atts = array();

  	$atts['class'] = $tag->get_class_option( $class );

  	$atts['id'] = $tag->get_id_option();

    $atts = wpcf7_format_atts( $atts );

    //get the cookies
  	$html = sprintf( '<div %1$s >', $atts );
    $html .= $this->ds_utmz_create_hidden_fields();
    $html .= '</div>';

  	return $html;
  }

  /* Tag generator */
  public function wpcf7_add_tag_generator_utm() {
  	$tag_generator = \WPCF7_TagGenerator::get_instance();
  	$tag_generator->add( 'utm', __( 'utm', 'contact-form-7' ),
  		array($this, 'wpcf7_tag_generator_utm'), array( 'nameless' => 1 ) );
  }

  public function wpcf7_tag_generator_utm( $contact_form, $args = '' ) {
  	$args = wp_parse_args( $args, array() );

  	$description = __( "Generate a form-tag to add UTM tracking info to this specific form. This will add a hidden field that will capture any UTM values from your visitors to this form. See %s.", 'contact-form-7' );

  	$desc_link = wpcf7_link( __( 'https://dustysun.com/contact-form-7-supercharger-utm-tracking/', 'contact-form-7' ), __( 'UTM Tracking Info', 'contact-form-7' ) );

  ?>
  <div class="control-box">
  <fieldset>
  <legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

  <table class="form-table">
  <tbody>

  	<tr>
  	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
  	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
  	</tr>

  	<tr>
  	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
  	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
  	</tr>

  </tbody>
  </table>
  </fieldset>
  </div>

  <div class="insert-box">
  	<input type="text" name="utm" class="tag code" readonly="readonly" onfocus="this.select()" />

  	<div class="utmbox">
  	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
  	</div>
  </div>
  <?php
  }

  public function ds_read_utmz_cookies() {
    $utm_values = array();
    //get the current url
    if(isset($_GET['utm_source']) && !empty($_GET['utm_source'])) {

      //variables for page URL
      $page_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

      $this->ds_utmz_timezone_set();
      $utm_access_date = new DateTime();
      $utm_access_date = $utm_access_date->format('D Y-m-d h:i:sa \U\T\CP T');

      $utm_values['utm_source'] = $_GET['utm_source'];
      $utm_values['utm_medium'] = isset($_GET['utm_medium']) && !empty($_GET['utm_medium']) ? $_GET['utm_medium'] : '';
      $utm_values['utm_campaign'] = isset($_GET['utm_campaign']) && !empty($_GET['utm_campaign']) ? $_GET['utm_campaign'] : '';
      $utm_values['utm_term'] = isset($_GET['utm_term']) && !empty($_GET['utm_term']) ? $_GET['utm_term'] : '';
      $utm_values['utm_content'] = isset($_GET['utm_content']) && !empty($_GET['utm_content']) ? $_GET['utm_content'] : '';
      $utm_values['utm_landing_page'] = $page_protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      $utm_values['utm_landing_page_date'] = $utm_access_date;

    } // end if(isset($_GET['utm_source']))
    else if(isset($_COOKIE['_ds_utmz'])) {
      $utmz_cookie = $_COOKIE['_ds_utmz'];
      $utm_terms = explode(',', $utmz_cookie);

      foreach($utm_terms as $utm_term) {
        $utm_term_array = explode('|', $utm_term);
        $utm_values[$utm_term_array[0]] = $utm_term_array[1];
      } //end foreach

      if(isset($utm_values['utm_landing_page_date']) && $utm_values['utm_landing_page_date'] != '') {
        // strip the bracketed time zone which is expanded on windows, such as
        // (Eastern Daylight Time) instead of (EDT) which causes a double time
        // zone error in PHP
        $stripped_date = strstr($utm_values['utm_landing_page_date'], " (", true);
        //get the server timezone and convert the cookie which was in the user's timezone
        $server_timezone = $this->ds_utmz_timezone_set();
        $utm_access_date = new DateTime($stripped_date);
        $utm_access_date->setTimezone(new DateTimeZone($server_timezone));
        $utm_values['utm_landing_page_date'] = $utm_access_date->format('D Y-m-d h:i:sa \U\T\CP T');
      }

    } //end if isset cookie

    return $utm_values;

  } //end function ds_read_utmz_cookies

  public function ds_utmz_timezone_set() {
    $timezone_string = get_option('timezone_string', '');
    if($timezone_string !== null && $timezone_string != '') {
      $timezone_name = get_option('timezone_string');
    } else {
      $offset = get_option('gmt_offset'); // GMT offset
      $is_DST = FALSE; // observing daylight savings?
      $timezone_name = timezone_name_from_abbr('', $offset * 3600, $is_DST); // e.g. "America/New_York"
    } //end if($timezone_string !== null && $timezone_string != '')

    // now set the timezone for PHP
    date_default_timezone_set($timezone_name);
    return $timezone_name;
  } //end function ds_utmz_timezone_set

  public function ds_utmz_create_hidden_fields() {
    //get the cookies
    $utm_values = $this->ds_read_utmz_cookies();

    $utm_hidden_fields = '';

    //replace any ampersands with &amp; to fix any appendXML issues that may occur with domDocument
    if(isset($utm_values['utm_landing_page'])) {
      $utm_values['utm_landing_page'] = str_replace('&', '&amp;', $utm_values['utm_landing_page']);
    }

    foreach($utm_values as $utm_key => $utm_value) {
      $utm_hidden_fields .='<input type="hidden" name="' . $utm_key . '" value="' . $utm_value . '" />';
    } //end foreach($utm_values as $utm_key => $utm_value)

    return $utm_hidden_fields;
  } //end function public function ds_utmz_create_hidden_fields(

}} //end class DSEWPCF7_UTM_Module
