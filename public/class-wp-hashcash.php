<?php
/**
 * The WordPress Hascash.IO integration plugin
 *
 * @package   WP_Hashcash
 * @author    Pavel A. Karoukin <webmaster@hashcash.io>
 * @author    Internetbureau Haboes <info@haboes.nl>
 * @license   GPL-2.0+
 * @copyright 2014 Hashcash.IO
*/

class WP_Hashcash {

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @var     string
     */
    const VERSION = '1.0.6';

    /**
     * Unique identifier.
     *
     * @var      string
     */
    protected $plugin_slug = 'wp-hashcash';

    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    private function __construct() {

		define('HASHCASH_ERROR', __( 'Submission failed. Make sure Javascript is turned on and try again. Contact the site administrator if this error persists.', $this->plugin_slug ));

        // Load plugin text domain
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

        // Activate plugin when new blog is added
        add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

        // Load public-facing CSS, JS and localization script
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Plugin filters
        add_filter( 'registration_errors',  array( $this, 'registration_errors_filter' ), 10, 3);
        add_filter( 'allow_password_reset', array( $this, 'password_reset_filter' ), 10, 2 );
        add_filter( 'wp_authenticate',      array( $this, 'authenticate_filter' ), 0 );
        add_filter( 'pre_comment_approved', array( $this, 'approve_comment_filter' ), 10, 2);

		// BuddyPress support
		add_action( 'bp_signup_validate', array( $this, 'authenticate_filter' ) );
    }

    /**
     * Return the plugin slug.
     *
     * @return string   Plugin slug variable.
     */
    public function get_plugin_slug() {
        return $this->plugin_slug;
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
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {

        $domain = $this->plugin_slug;
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

    }

    /**
     * Register and enqueues public-facing JavaScript files.
     */
    public function enqueue_scripts() {

        // CSS
        wp_enqueue_style( 'hashcodecss', '//cdnjs.cloudflare.com/ajax/libs/jquery.hashcash.io/0.0.2/jquery.hashcash.io.min.css', array() );
        wp_enqueue_style( $this->plugin_slug, plugins_url( 'assets/css/wp-hashcash.css', __FILE__ ), array( 'hashcodecss' ), self::VERSION );

        // JS
        wp_enqueue_script( 'hashcodejs', '//cdnjs.cloudflare.com/ajax/libs/jquery.hashcash.io/0.0.2/jquery.hashcash.io.min.js', 'jquery', '0.0.1', true );
        wp_enqueue_script( $this->plugin_slug, plugins_url( 'assets/js/wp-hashcash.js', __FILE__ ), array( 'jquery', 'hashcodejs' ), self::VERSION, true );

        // Localization script
		$default_complexity = get_option( 'hashcash_complexity' );

		wp_localize_script( 'hashcodejs', 'HashcashSettings', array(
			'key'        => get_option( 'hashcash_public_key' ),
			'complexity' => $default_complexity,
			'lang'       => get_option( 'hashcash_translations' ),

			// For now hard-code selectors to lock. In future this needs to be customizable from admin panel
			'selectors'  => array(
				'#loginform [type="submit"]'                => $default_complexity,
				'#lostpasswordform [type="submit"]'         => $default_complexity,
				'#registerform [type="submit"]'             => $default_complexity,
				'.comment-form [type="submit"]'             => $default_complexity,
				'#buddypress #signup_form #signup_submit'   => $default_complexity,
				'#commentform .input_submit'                => $default_complexity,
				'#commentform #submit'                      => $default_complexity,

				// Woocommerce login button at /my-account
				'.woocommerce form.login input[name=login]' => $default_complexity,
			),
		));

    }

    /**
     * Perform Hashcash.io validation on user registration
     * 
     * @param  object $errors               Any errors that have been processed up to this point.
     * @param  string $sanitized_user_login The sanitized username as entered by the user.
     * @param  string $user_email           The email as entered by the user.
     * @return object                       Return error object
     */
    public function registration_errors_filter( $errors, $sanitized_user_login, $user_email ) {
	    
	    if ( ! empty( $_POST ) ) {
	        $ret = $this->verify_hash( $_POST['hashcashid'] );

	        if ($ret == 'no') {
	            $errors->add('invalid', HASHCASH_ERROR);
	        }

	        if ($ret == 'fast') {
	            $errors->add('toofast', HASHCASH_ERROR);
	        }
	    }

	    return $errors;
	}

	/**
	 * Perform Hashcash.io validation on password reset
	 * 
	 * @param  bool $allow_password_reset  Whether to allow the password to be reset. Default true.
	 * @param  int  $user_data->ID         The ID of the user attempting to reset a password.
	 * @return bool true|false             Return true if is passed the test.
	 */
	public function password_reset_filter( $allow_password_reset, $userid ) {
	    if ( ! empty($_POST)) {
	        $ret = $this->verify_hash( $_POST['hashcashid'] );

	        if ( $ret != 'ok' ) {
	            $allow_password_reset = false;
	        }
	    }

	    return $allow_password_reset;
	}

	

	/**
	 * Perform Hashcash.io validation on user authentication
	 * 
	 * @param  object $user     The WP_User() object of the user.
	 * @param  string $password The user's password (encrypted).
	 * @return object           This hook should return either a WP_User() object or, 
	 *                          if generating an error, a WP_Error() object.
	 */
	function authenticate_filter() {
	    if ( ! empty( $_POST ) && isset( $_POST['pwd'] ) ) {
	        $ret = $this->verify_hash( $_POST['hashcashid'] );

	        if ( $ret == 'no' || $ret == 'fast' ) {
				die(HASHCASH_ERROR);
	        }
	    }
	}

	/**
	 * Perform Hashcash.io validation on new comment approval
	 * 
	 * @param  mixed $approved    Preliminary comment approval status: 0, 1, or 'spam'.
	 * @param  array $commentdata Comment data array
	 * @return mixed $approved    Comment status: 0, 1, or 'spam'.
	 */
	public function approve_comment_filter( $approved, $commentdata ) {
		
		// ignore trackbacks and pingbacks
		if ( $comment['comment_type'] == '' ) {   
	        $ret = $this->verify_hash( $_POST['hashcashid'] );

			if ( $ret == 'no' || $ret == 'fast' ) {
				die(HASHCASH_ERROR);
	        }
	        if ( $ret != 'ok' ) {
	            $approved = 'spam';
	        }
	    }

	    return $approved;
	}

	private function mailError($message) {
		$mailed_last = get_option('hashcash_mailed_last');

		// Don't sent email more often than once every 10 minutes to not annoy site administrator
		if ($mailed_last && $mailed_last + 600 > time()) {
			return;
		}
		//update_option('hashcash_mailed_last', time());

		$to = get_option( 'admin_email' );
		$subject = '[' . get_option( 'blogname' ) . '] Hashcash.IO plugin error.';

		// Add debugging info
		$message.= "Details:\n\n";
		$message.= print_r($_REQUEST,1) . "\n\n";
		$message.= print_r($_SERVER,1) . "\n\n";

		wp_mail($to, $subject, $message);
	}

	/**
	 * Verify hash and secret key at the Hashcash.io server
	 * 
	 * @param  string $hash The public hash key, provided by localization script
	 * @return string       "ok"   : verified
	 *                      "no"   : not verified
	 *                      "fast" : too fast
	 */
	public function verify_hash( $hash, $complexity = FALSE ) {
	    // If this setting is not set, plugin is not configured. Disable verification.
	    $key = get_option('hashcash_private_key');

	    if ( empty( $key ) ) {
	        return 'ok';
	    }

	    if ( empty( $hash ) ) {
	        return 'no';
	    }

		if (! $complexity) {
			$complexity = get_option( 'hashcash_complexity' );
		}

	    $hash = preg_replace( '/[^\w-\d]/', '', $hash );

	    $url = 'https://hashcash.io/api/checkwork/' . $hash . '?apikey=' . $key;

	    $jsonWork = wp_remote_get( $url );

		if ( is_wp_error( $jsonWork ) ) {
			$error_message = $jsonWork->get_error_message();

			$message = "While processing form submission Hashcash.IO plugin experienced connectivity error. Therefore allowed submission to pass.\n\n";
			$message.= 'Error message: ' . $error_message . "\n\n";

			$this->mailError($message);

			return 'ok';
		}

		// Incorrect keys
		if ( $jsonWork['response'] && $jsonWork['response']['code'] == 403 ) {
			$message = "Hashcash.IO keys were not accepted and user was allowed to submit form without verification.\n\n";
			$message.= "Please login to Admin Dashboard and update settings of Hashcash.IO plugin with correct keys.\n\n";

			$this->mailError($message);

			return 'ok';
		}

	    $work = json_decode( wp_remote_retrieve_body( $jsonWork ) );

	    if ( ! $work ) {
	        return 'no';
	    } else if ( ! $work->totalDone ) {
	        return 'no';
	    } else if ( $work->totalDone < $complexity ) {
	        return 'fast';
	    }

	    return 'ok';
	}
}

// vim: ts=4:sw=4:noet
