/* global HashcashSettings, jQuery */
(function($) {
    // If plugin is not configured yet - exit
    if ( ! HashcashSettings || ! HashcashSettings.key ) {
        return;
    }

    // List of all buttons we need to lock down
    var buttons = [
        '#loginform [type="submit"]',
        '#lostpasswordform [type="submit"]',
        '#registerform [type="submit"]',
        '.comment-form [type="submit"]',
        '#buddypress #signup_form #signup_submit',
        '#commentform .input_submit',
        '#commentform #submit',
        '.woocommerce form.login input[name=login]' // Woocommerce login button at /my-account
    ];

    $( buttons.join(',') ).hashcash({
        key: HashcashSettings.key,
        complexity: HashcashSettings.complexity,
        lang: HashcashSettings.lang
    });


})(jQuery);
