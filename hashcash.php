<?php
/**
 * The WordPress Hashcash.IO integration plugin
 *
 * @package   WP_Hashcash
 * @author    Pavel A. Karoukin <webmaster@hashcash.io>
 * @author    Internetbureau Haboes <info@haboes.nl>
 * @license   GPL-2.0+
 * @copyright 2014 Hashcash.IO
 *
 * @wordpress-plugin
 * Plugin Name: Hashcash.IO Integration
 * Plugin URI: http://wordpress.org/plugins/hashcash/
 * Description: Hashcash.IO Integration
 * Author: Pavel A. Karoukin, haboes
 * Version: 1.0.14
 * GitHub Plugin URI: https://github.com/hashcash/wordpress-hashcash/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) 
    die;

require_once( plugin_dir_path( __FILE__ ) . 'includes/contactform7.php' );

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

if ( ! class_exists( 'WP_Hashcash' ) ) {

    require_once( plugin_dir_path( __FILE__ ) . 'public/class-wp-hashcash.php' );
    add_action( 'plugins_loaded', array( 'WP_Hashcash', 'get_instance' ) );

}


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() 
    && ! class_exists( 'WP_HashcashAdmin' )
    && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

    require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wp-hashcash-admin.php' );
    add_action( 'plugins_loaded', array( 'WP_Hashcash_Admin', 'get_instance' ) );

}

