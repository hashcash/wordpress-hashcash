<?php
/**
** A base module for [hashcash]
**/

/* Shortcode handler */
add_action( 'wpcf7_init', 'wpcf7_add_shortcode_hashcash' );

function wpcf7_add_shortcode_hashcash() {
	wpcf7_add_shortcode( array( 'hashcash' ),
		'wpcf7_hashcash_shortcode_handler', true );
}

function wpcf7_hashcash_shortcode_handler( $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	if ( $tag->type != 'hashcash' )
		return '';

	$op = wpcf7_hashcash_options($tag->options);

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	$class .= ' wpcf7-hashcash-' . $tag->name;

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();

	$html = sprintf(
		'<input type="hidden" name="_wpcf7_hashcash_complexity" value="%1$s" class="%2$s" id="%3$s" />',
		$op['complexity'], $atts['class'], $atts['id']
	);

	return $html;
}

function wpcf7_hashcash_options($options) {
	$op = array();

	foreach ($options as $option) {
		list($key, $value) = explode(':', $option);
		if (! isset($value)) {
			$op[$key] = TRUE;
		}
		else {
			$op[$key] = $value;
		}
	}

	return $op;
}

/* Validation filter */
add_filter( 'wpcf7_validate_hashcash', 'wpcf7_hashcash_validation_filter', 10, 2 );

function wpcf7_hashcash_validation_filter( $result, $tag ) {
	if ( ! class_exists( 'WP_Hashcash' ) ) {
		die("WP_Hashcash Class is not available.");
	}

	$instance = WP_Hashcash::get_instance();
	$tag = new WPCF7_Shortcode( $tag );
	$hashcashid = $_POST['hashcashid'];

	if (! $hashcashid) {
		die("hashcashid value is not available.");
	}

	$type = $tag->type;
	$name = $tag->name;
	$op = wpcf7_hashcash_options( $tag->options );

	$hashcash_result = $instance->verify_hash( $hashcashid, $op['complexity'] );

	if ( $hashcash_result !== 'ok' ) {
		$result['valid'] = false;
		$result['reason'][$name] = wpcf7_get_message( 'hashcash_error' );
	}

	if ( isset( $result['reason'][$name] ) && $id = $tag->get_id_option() ) {
		$result['idref'][$name] = $id;
	}

	return $result;
}

/* Messages */
add_filter( 'wpcf7_messages', 'wpcf7_hashcash_messages' );

function wpcf7_hashcash_messages( $messages ) {
	return array_merge( $messages, array( 'hashcash_error' => array(
		'default' => __( 'Form validation failed. Make sure you have Javascript enabled and try to re-submit this form.', 'contact-form-7' )
	) ) );
}


/* Tag generator */

add_action( 'admin_init', 'wpcf7_add_tag_generator_hashcash', 45 );

function wpcf7_add_tag_generator_hashcash() {
	if ( ! function_exists( 'wpcf7_add_tag_generator' ) )
		return;

	wpcf7_add_tag_generator( 'hashcash', __( 'Hashcash.IO', 'contact-form-7' ),
		'wpcf7-tg-pane-hashcash', 'wpcf7_tg_pane_hashcash' );
}

function wpcf7_tg_pane_hashcash( $contact_form ) {
?>
<div id="wpcf7-tg-pane-hashcash" class="hidden">
	<form action="">
		<table>
			<tr><td><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
		</table>

		<table class="scope hashcash">
			<caption><?php echo esc_html( __( "Hashcash.IO widget settings", 'contact-form-7' ) ); ?></caption>

			<tr>
				<td><code>id</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
				<input type="text" name="id" class="idvalue oneline option" /></td>

				<td><code>class</code> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
				<input type="text" name="class" class="classvalue oneline option" /></td>
			</tr>

			<tr>
				<td><?php echo esc_html( __( "Complexity", 'contact-form-7' ) ); ?> (<?php echo esc_html( __( 'optional', 'contact-form-7' ) ); ?>)<br />
				<input type="text" name="complexity" class="oneline option" value="0.01" /></td>
			</tr>
		</table>

		<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form on the left. Only one Hashcash.IO widget per one form is allowed!", 'contact-form-7' ) ); ?>
			<input type="text" name="hashcash" class="tag wp-ui-text-highlight code" readonly="readonly" onfocus="this.select()" />
		</div>
	</form>
</div>
<?php
}

