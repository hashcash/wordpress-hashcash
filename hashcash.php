<?php
/**
    Plugin Name: Hashcash.IO Integration
    Plugin URI: http://wordpress.org/plugins/hashcash/
    Description: Hashcash.IO Integration
    Author: Pavel A. Karoukin
    Version: 1.0
*/

defined('ABSPATH') or die("No script kiddies please!");

function _add_js_css() {
    wp_enqueue_script( 'jquery');
    wp_enqueue_script( 'hashcode', '//cdnjs.cloudflare.com/ajax/libs/jquery.hashcash.io/0.0.1/jquery.hashcash.io.min.js', 'jquery', '1.0', true );
    wp_enqueue_script( 'wphashcode', plugins_url( 'hashcash.js', __FILE__ ), 'jquery', '1.0', true );

    wp_enqueue_style( 'hashcodecss', '//cdnjs.cloudflare.com/ajax/libs/jquery.hashcash.io/0.0.1/jquery.hashcash.io.min.css', array() );
    wp_enqueue_style( 'wphashcodecss', plugins_url('wp-local.css', __FILE__ ), array() );

    $settings = array(
        "key"        => get_option('hashcash_public_key'),
        "complexity" => get_option('hashcash_complexity'),
        "lang" => array(
            "screenreader_notice"      => __('Click this to unlock submit button', 'hashcash'),
            "screenreader_notice_done" => __('Form unlocked. Please submit this form.', 'hashcash'),
            "screenreader_computing"   => __('Please wait while computing.', 'hashcash'),
            "screenreader_computed"    => __('Form is ready. Please submit this form.', 'hashcash'),
            "screenreader_done"        => __('__done__% done.', 'hashcash'),
            "popup_info"               => __('Please unlock it first.', 'hashcash'),
        ),
    );

    wp_localize_script( 'hashcode', 'HashcashSettings', $settings );
}

function hashcash_enqueue() {
    _add_js_css();
}

add_action('login_enqueue_scripts', 'hashcash_enqueue');
add_action('admin_menu', 'hashcash_add_setting_page');

function hashcash_add_setting_page() {
    add_options_page( __('Hashcash.IO Settings','hashcash'), __('Hashcash.IO','hashcash'), 'manage_options', 'hashcash-settings', 'hashcash_settings_page' );
}

function hashcash_settings_api_init() {

    add_settings_section(
        'hashcash-settings-section',
        '',
        'hashcash_setting_section_callback_function',
        'hashcash-setting'
    );

    add_settings_field(
        'hashcash_public_key',
        __('Public key','hashcash'),
        'hashcash_public_key_callback_function',
        'hashcash-setting',
        'hashcash-settings-section'
    );

    register_setting( 'hashcash-setting', 'hashcash_public_key', 'hashcash_validate_public_key' );

    add_settings_field(
        'hashcash_private_key',
        __('Private key','hashcash'),
        'hashcash_private_key_callback_function',
        'hashcash-setting',
        'hashcash-settings-section'
    );

    register_setting( 'hashcash-setting', 'hashcash_private_key', 'hashcash_validate_private_key' );

    add_settings_field(
        'hashcash_complexity',
        __('Complexity','hashcash'),
        'hashcash_complexity_callback_function',
        'hashcash-setting',
        'hashcash-settings-section'
    );

    register_setting( 'hashcash-setting', 'hashcash_complexity', 'hashcash_validate_complexity' );

}

add_action( 'admin_init', 'hashcash_settings_api_init' );

function hashcash_setting_section_callback_function() {
    print "<p>";
    _e('You need to obtain public/private keys pair at <a href="https://hashcash.io">hashcash.io</a>','hashcash');
    print "</p>";
}

function hashcash_public_key_callback_function() {
    $h = get_option('hashcash_public_key');
    print '<input name="hashcash_public_key" id="hashcash_public_key" type="text" value="'.esc_attr($h).'" />';
    print '<p class="description">Public key look like fece3f6e-9966-49cc-9079-88723bcfe847</p>';
}

function hashcash_private_key_callback_function() {
    $h = get_option('hashcash_private_key');
    print '<input name="hashcash_private_key" id="hashcash_private_key" type="text" value="'.esc_attr($h).'" />';
    print '<p class="description">Private key look like PRIVATE-ed0c6b0e-8788-4cee-8213-842fd90885c3</p>';
}

function hashcash_complexity_callback_function() {
    $h = get_option('hashcash_complexity');

    if (! $h) {
        $h = 0.01;
    }

    print '<input name="hashcash_complexity" id="hashcash_complexity" type="text" value="'.esc_attr($h).'" />';
    print '<p class="description">You can adjust how much work browser need to do to unlock widget with complexity. Larger value - longer it takes to finish work. Good starting point is 0.01</p>';
}

function hashcash_validate_public_key($v) {
    if (!preg_match('/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/',$v)) {
        add_settings_error( 'hashcash_public_key', 'hashcash_private_key', __('Not proper format of public key','hashcash'), 'error' );
        $v = get_option('hashcash_public_key');
    }
    return $v;
}

function hashcash_validate_private_key($v) {
    if (!preg_match('/^PRIVATE-\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/',$v)) {
        add_settings_error( 'hashcash_private_key', 'hashcash_private_key', __('Not proper format of private key','hashcash'), 'error' );
        $v = get_option('hashcash_private_key');
    }
    return $v;
}

function hashcash_validate_complexity($v) {
    if (!is_numeric($v)) {
        add_settings_error( 'hashcash_complexity', 'hashcash_complexity', __('Complexity have to be a number. Good starting point is 0.01 complexity and adjust as necessary.','hashcash'), 'error' );
        $v = get_option('hashcash_complexity');
    }
    return $v;
}

function hashcash_settings_page() {
    print '<div class="wrap">';
    print '<h2>' . __('Hashcash Settings','hashchash') . '</h2>';
    print '<form method="POST" action="options.php">';

    settings_fields( 'hashcash-setting' );
    do_settings_sections( 'hashcash-setting' );
    submit_button();

    print '</form>';
    print '</div>';
}

add_filter('registration_errors', 'hashcash_register', 10, 3);

function hashcash_register($errors, $login, $pass) {
    if (! empty($_POST)) {
        $ret = hashcash_verifyhash($_POST['hashcashid']);

        $error_message = __( "Submission failed. Please try again or contact the site administrator if this error persists.", "hashcash" );

        if ($ret == 'no') {
            $errors->add('invalid', $error_message);
        }

        if ($ret == 'fast') {
            $errors->add('toofast', $error_message);
        }
    }

    return $errors;
}

add_filter('allow_password_reset', 'hashcash_password_reset', 10, 2);

function hashcash_password_reset($allow,$id ) {

    if (! empty($_POST)) {
        $ret = hashcash_verifyhash($_POST['hashcashid']);

        if ($ret != 'ok') {
            $allow = false;
        }
    }

    return $allow;
}

add_action('login_form_login','hashcash_login' );

function hashcash_login() {
    add_filter( 'wp_authenticate_user', 'hashcash_authenticate', 10, 2);
}

function hashcash_authenticate($user, $pass) {

    if (! empty($_POST)) {
        $ret = hashcash_verifyhash($_POST['hashcashid']);

        $error_message = __( "Submission failed. Please try again or contact the site administrator if this error persists.", "hashcash" );

        if ($ret == 'no') {
            $user = new WP_Error('invalid', $error_message);
        }

        if ($ret == 'fast') {
            $user = new WP_Error('toofast', $error_message);
        }
    }
    return $user;
}

// contact hashcash.io server to verify the code.
//
// returns:
//   ok - verified
//   no - not verified
//   fast - too fast
function hashcash_verifyhash($hash) {
    // If this setting is not set, plugin is not configured. Disable verification.
    $key = get_option('hashcash_private_key');

    if (empty($key)) {
        return 'ok';
    }

    if (empty($hash)) {
        return 'no';
    }

    $hash = preg_replace('/[^\w-\d]/', '', $hash);

    $url = 'https://hashcash.io/api/checkwork/' . $hash . '?apikey=' . $key;

    $jsonWork = wp_remote_get($url);

    $work = json_decode(wp_remote_retrieve_body($jsonWork));

    if (! work) {
        return 'no';
    } else if ( ! $work->totalDone ) {
        return 'no';
    } else if ( $work->totalDone < get_option('hashcash_complexity')) {
        return 'fast';
    }

    return 'ok';
}

// comment handling section
add_action( 'wp_enqueue_scripts', 'hashcash_com_enqueue' );
function hashcash_com_enqueue() {
    if (is_singular() && comments_open()) {
        _add_js_css();
    }
}

add_filter('pre_comment_approved', 'hashcash_check_spam', 10, 2);
function hashcash_check_spam($approved,$comment) {
    if ($comment['comment_type'] == '') {   // ignore trackbacks and pingbacks
        $ret = hashcash_verifyhash($_POST['hashcashid']);
        if ($ret != 'ok') {
            $approved = 'spam';
        }
    }
    return $approved;
}
