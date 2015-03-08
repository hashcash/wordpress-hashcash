/* eslint-env browser */
/* global HashcashSettings, jQuery */
(function($) {
    // If plugin is not configured yet - exit
    if ( !HashcashSettings || !HashcashSettings.key || !HashcashSettings.selectors ) {
        return;
    }

    Object.keys(HashcashSettings.selectors).forEach(function(selector) {
        var complexity = HashcashSettings.selectors[selector];

        $( selector ).hashcash({
            key        : HashcashSettings.key,
            complexity : complexity,
            lang       : HashcashSettings.lang
        });
    });

    function attachHashcash($el, complexity) {
        $el.hashcash({
            key        : HashcashSettings.key,
            complexity : complexity,
            lang       : HashcashSettings.lang
        });
    }

    $('input[name=_wpcf7_hashcash_complexity]').each(function() {
        var complexity = $(this).val();
        var form = $(this).parents('form').eq(0);
        var submitButton = form.find('input[type=submit]').eq(0);

        attachHashcash(submitButton, complexity);

        form.submit(function() {
            setTimeout(function() {
                attachHashcash(submitButton, complexity);
            }, 500);

            return true;
        });
    });
})(jQuery);
