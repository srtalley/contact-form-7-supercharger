<?php
	/**
	** A module to allow adding a redirection url in the contact form.
	** Usage: [redirectpage "http://validurl.com"]
	**/
	namespace DustySun\CF7_Supercharger;

	if(!class_exists('DustySun\CF7_Supercharger\RedirectPage_Module'))  { class RedirectPage_Module {


	/* form_tag handler */
	// Create the object
	public function __construct($data = null) {
		add_action( 'wpcf7_init', array($this, 'wpcf7_add_form_tag_redirectpage' ));

		add_action( 'wpcf7_admin_init', array($this, 'wpcf7_add_tag_generator_redirectpage'), 100 );

	} //end __construct

	function wpcf7_add_form_tag_redirectpage() {
		wpcf7_add_form_tag( 'redirectpage',
			array($this, 'wpcf7_redirectpage_form_tag_handler'));
	}

	function wpcf7_redirectpage_form_tag_handler( $tag ) {

		$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-redirectpage' );

		$atts = array();

		$atts['class'] = $tag->get_class_option( $class );
		$atts['id'] = $tag->get_id_option();

		$value = (string) reset( $tag->values );

		$atts['value'] = $value;
		$atts['type'] = 'hidden';
		$atts['name'] = 'redirectpage';

		$atts = wpcf7_format_atts( $atts );

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><input %2$s /></span>',
			sanitize_html_class( $tag->name ), $atts );

		return $html;
	}

	/* Tag generator */

	function wpcf7_add_tag_generator_redirectpage() {
		$tag_generator = \WPCF7_TagGenerator::get_instance();
		$tag_generator->add( 'redirectpage', __( 'redirectpage', 'contact-form-7' ),
			array($this, 'wpcf7_tag_generator_redirectpage' ), array( 'nameless' => 1 ) );
	}
	public function wl ( $log )  {
    if ( true === WP_DEBUG ) {
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
      }
  } // end write_log
	function wpcf7_tag_generator_redirectpage( $contact_form, $args = '' ) {

		$args = wp_parse_args( $args, array() );
		$type = $args['id'];

		$description = __( "Generate a form-tag for a redirect page URL. This adds a hidden input element to your form that the <strong>Contact Form 7 SUPERCHARGER</strong> plugin uses to redirect your form to the specified URL after successful submission. For more details, see %s.", 'contact-form-7' );

		$desc_link = wpcf7_link( __( 'https://dustysun.com/contact-form-7-supercharger-redirect-page/', 'contact-form-7' ), __( 'redirectpage help', 'contact-form-7' ) );

	?>
	<div class="control-box">
	<fieldset>
	<legend><?php echo sprintf( $description, $desc_link ); ?></legend>

	<table class="form-table">
	<tbody>

		<tr>
		<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Redirect URL (Required)', 'contact-form-7' ) ); ?></label></th>
		<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" style="width:100%;"/></td>
		</tr>

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
		<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

		<div class="submitbox">
		<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
		</div>

	</div>
	<?php
	}
}} //end class
