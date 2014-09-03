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

class WP_Hashcash_Admin {

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @var     string
     */
    const VERSION = '1.1';

    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Slug of the plugin screen.
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;

    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     */
    private function __construct() {

        $plugin = WP_Hashcash::get_instance();
        $this->plugin_slug = $plugin->get_plugin_slug();

        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

        // Settings
        add_action( 'admin_init', array( $this, 'settings_api' ) );

    }

    /**
     * Return an instance of this class.
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     */
    public function add_plugin_admin_menu() {

        /*
         * Add a settings page for this plugin to the Settings menu.
         */
        $this->plugin_screen_hook_suffix = add_options_page(
            __( 'Hashcash.io Settings', $this->plugin_slug ),
            __( 'Hashcash.IO', $this->plugin_slug ),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_plugin_admin_page' )
        );

    }

    /**
	 * Render the settings page for this plugin.
	 */
    public function display_plugin_admin_page() {
		// CSS
		wp_enqueue_style( 'hashcashcss', plugins_url( '../public/jquery.hashcash.io/jquery.hashcash.io.min.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_style( $this->plugin_slug . '-admin', plugins_url( 'assets/css/wp-hashcash-admin.css', __FILE__ ), array( 'hashcashcss' ), self::VERSION );

		// JS
		wp_enqueue_script( 'hashcashjs', plugins_url( '../public/jquery.hashcash.io/jquery.hashcash.io.min.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		wp_enqueue_script( $this->plugin_slug . '-admin', plugins_url( 'assets/js/wp-hashcash-admin.js', __FILE__ ), array( 'jquery', 'hashcashjs' ), self::VERSION, true );

    	include_once( 'views/admin.php' );
    }

    /**
     * Setup options/settings page, using the WordPress settings API
     */
    public function settings_api() {
    	
    	/**
    	 * Default Hashcash settings
    	 */
    	add_settings_section(
	        'hashcash-settings-section',
	        __( 'Hashcash.io general settings', $this->plugin_slug ),
	        array( $this, 'setting_section_callback_function' ),
	        'hashcash-setting'
	    );

	    add_settings_field(
	        'hashcash_public_key',
	        __('Public key','hashcash'),
	        array( $this, 'public_key_callback_function' ),
	        'hashcash-setting',
	        'hashcash-settings-section'
	    );

	    add_settings_field(
	        'hashcash_private_key',
	        __('Private key','hashcash'),
	        array( $this, 'private_key_callback_function' ),
	        'hashcash-setting',
	        'hashcash-settings-section'
	    );

	    add_settings_field(
	        'hashcash_complexity',
	        __('Complexity','hashcash'),
	        array( $this, 'complexity_callback_function' ),
	        'hashcash-setting',
	        'hashcash-settings-section'
	    );

	    register_setting( 'hashcash-setting', 'hashcash_public_key', array( $this, 'validate_public_key' ) );
	    register_setting( 'hashcash-setting', 'hashcash_private_key', array( $this, 'validate_private_key' ) );
	    register_setting( 'hashcash-setting', 'hashcash_complexity', array( $this, 'validate_complexity' ) );

	    /**
    	 * Hashcash Translations
    	 */
    	$option_name   = 'hashcash_translations';
    	$data          = get_option( $option_name );

    	register_setting( 
    		'hashcash-setting', 
    		$option_name
    	);

    	add_settings_section(
	        'hashcash-translation-section',
	        __( 'Manage notices', $this->plugin_slug ),
	        array( $this, 'render_translation_intro' ),
	        'hashcash-setting'
	    );

	    add_settings_field(
	        'progress_time_left',
	        __('Progress text (with time left)', $this->plugin_slug ),
	        array( $this, 'render_textfield' ),
	        'hashcash-setting',
	        'hashcash-translation-section',
	        array (
	        	'option_name'     => $option_name,
	        	'name'            => 'progress_time_left',
	        	'value'           => esc_attr( $data['progress_time_left'] ),
	        	'default'         => 'Please wait __timeLeft__ more seconds before submitting form.'
	        )
	    );

	    add_settings_field(
	        'progress',
	        __('Progress text', $this->plugin_slug ),
	        array( $this, 'render_textfield' ),
	        'hashcash-setting',
	        'hashcash-translation-section',
	        array (
	        	'option_name'     => $option_name,
	        	'name'            => 'progress',
	        	'value'           => esc_attr( $data['progress'] ),
	        	'default'         => 'Please wait before submitting form.'
	        )
	    );

	    add_settings_field(
	        'notSupportedBrowser',
	        __('Not supported browser notice', $this->plugin_slug ),
	        array( $this, 'render_textfield' ),
	        'hashcash-setting',
	        'hashcash-translation-section',
	        array (
	        	'option_name'     => $option_name,
	        	'name'            => 'notSupportedBrowser',
	        	'value'           => esc_attr( $data['notSupportedBrowser'] ),
	        	'default'         => 'Your browser is not supported. Please use latest version of Chrome, Firefox or Internet Explorer.'
	        )
	    );

	    add_settings_field(
	        'runtimeError',
	        __('Runtime error notice', $this->plugin_slug ),
	        array( $this, 'render_textfield' ),
	        'hashcash-setting',
	        'hashcash-translation-section',
	        array (
	        	'option_name'     => $option_name,
	        	'name'            => 'runtimeError',
	        	'value'           => esc_attr( $data['runtimeError'] ),
	        	'default'         => 'Runtime error. Please try to refresh page.'
	        )
	    );

	    add_settings_field(
	        'formReady',
	        __('Form is ready notice', $this->plugin_slug ),
	        array( $this, 'render_textfield' ),
	        'hashcash-setting',
	        'hashcash-translation-section',
	        array (
	        	'option_name'     => $option_name,
	        	'name'            => 'formReady',
	        	'value'           => esc_attr( $data['formReady'] ),
	        	'default'         => 'You can submit form now.'
	        )
	    );

    }

    /**
     * Render tranlation section intro
     * 
     * @return
     */
    public function render_translation_intro() {
    	return;
    }

    /**
     * Render default textfield, using the Settings API
     * 
     * @param  array $args An array containing option_name, name, 
     *                     value, default and optional description
     * @return string      A html input type="text" element
     */
    public function render_textfield( $args ) {
    	if ( empty( $args ) ) 
    		return;

    	$value = ( $args['value'] != '' ) ? $args['value'] : $args['default'];

    	printf( '<input name="%1$s[%2$s]" id="%2$s" value="%3$s" placeholder="%4$s" class="regular-text" type="text">',
    		$args['option_name'],
    		$args['name'],
    		$value,
    		$args['default']
    	);

    	if ( isset( $args['description'] ) && '' !== $args['description'] ) {
    		print $args['description'];
    	}
    }

    public function render_checkbox( $args ) {
    	if ( empty( $args ) ) 
    		return;

    	$value   = ( 'on' === $args['value'] ) ? ' checked="checked"' : '';

    	printf( '<input name="%1$s[%2$s]" id="%2$s" %3$s type="checkbox">',
    		$args['option_name'],
    		$args['name'],
    		$value
    	);

    	if ( isset( $args['description'] ) && '' !== $args['description'] ) {
    		print $args['description'];
    	}
    }

    /**
	 * Callback function for Hashcash section
	 */
    public function setting_section_callback_function() {
	    print "<p>";
	    _e('You need to obtain public/private keys pair at <a href="https://hashcash.io">hashcash.io</a>', $this->plugin_slug );
	    print "</p>";
	}

	/**
	 * Callback function for public key
	 */
	public function public_key_callback_function() {
	    $h = get_option('hashcash_public_key');
	    print '<input class="regular-text" name="hashcash_public_key" id="hashcash_public_key" type="text" value="'.esc_attr($h).'" />';
	    print '<p class="description">Public key looks like <code>fece3f6e-9966-49cc-9079-88723bcfe847</code></p>';
	}

	/**
	 * Callback function for private key
	 */
	public function private_key_callback_function() {
	    $h = get_option('hashcash_private_key');
	    print '<input class="regular-text" name="hashcash_private_key" id="hashcash_private_key" type="text" value="'.esc_attr($h).'" />';
	    print '<p class="description">Private key looks like <code>PRIVATE-ed0c6b0e-8788-4cee-8213-842fd90885c3</code></p>';
	}

	/**
	 * Callback function for complexity value
	 */
	public function complexity_callback_function() {
	    $h = get_option('hashcash_complexity');

	    if (! $h) {
	        $h = 0.01;
	    }

	    print '<input class="small-text" name="hashcash_complexity" id="hashcash_complexity" type="number" min="0.01" step="0.01" value="'.esc_attr($h).'" />';
	    print '<p class="description">You can adjust how much work browser need to do to unlock widget with complexity. Larger value - longer it takes to finish work. Good starting point is 0.01</p>';
	}

	/**
	 * Validation function for public key
	 *
	 * @param  string $string The public key value.
	 * @return string $string The public key value. Displays error 
	 *                        if it doesn't match regular expression.
	 */
	public function validate_public_key( $string ) {
	    if ( ! preg_match('/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/', $string ) ) {
	        add_settings_error( 'hashcash_public_key', 'hashcash_private_key', __('Not proper format of public key', $this->plugin_slug ), 'error' );
	        $string = get_option( 'hashcash_public_key' );
	    }
	    return $string;
	}

	/**
	 * Validation function for private key
	 *
	 * @param  string $string The private key value.
	 * @return string $string The private key value. Displays error 
	 *                        if it doesn't match regular expression.
	 */
	public function validate_private_key( $string ) {
	    if ( ! preg_match('/^PRIVATE-\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/', $string ) ) {
	        add_settings_error( 'hashcash_private_key', 'hashcash_private_key', __('Not proper format of private key', $this->plugin_slug ), 'error' );
	        $string = get_option( 'hashcash_private_key' );
	    }
	    return $string;
	}

	/**
	 * Validation function for complexity value
	 * 
	 * @param  string $string The complexity value.
	 * @return string $string The complexity value. Displays error if it's not a number.
	 */
	public function validate_complexity( $string ) {
	    if ( ! is_numeric( $string ) ) {
	        add_settings_error( 'hashcash_complexity', 'hashcash_complexity', __('Complexity have to be a number. Good starting point is 0.01 complexity and adjust as necessary.','hashcash'), 'error' );
	        $string = get_option( 'hashcash_complexity' );
	    }
	    return $string;
	}

}

# vim: set noexpandtab:
