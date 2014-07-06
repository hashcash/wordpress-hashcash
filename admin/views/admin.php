<?php
/**
 * The WordPress Hashcash.IO integration plugin
 *
 * @package   WP_Hashcash
 * @author    Pavel A. Karoukin  <webmaster@hashcash.io>
 * @author    Internetbureau Haboes <info@haboes.nl>
 * @license   GPL-2.0+
 * @copyright 2014 Hashcash.IO
*/
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form method="POST" action="options.php">
		
		<?php
			/**
			 * Load Settings fields, using the WP Settings API
			 */
			settings_fields( 'hashcash-setting' );
		    do_settings_sections( 'hashcash-setting' );
		    submit_button();
		?>

	</form>

	<form id="quickkey" action="#"></form>

</div>
<?php # vim: set noexpandtab: ?>
