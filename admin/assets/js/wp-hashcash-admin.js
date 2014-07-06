/* global jQuery */
(function($) {

    var $container = $("#hashcash_private_key").parents('td').eq(0);
    $container.append("<p class='hashcash-quickkey'><a href='#' class='quick-generate-link'>Generate New Keys</a></p>");

    $(".quick-generate-link").hashcash({
        formEl: '#quickkey',
        complexity: 0.01,
        key: "f2c91456-823e-482e-a62b-ba8cd7920308"
    });

    $(".quick-generate-link").click(function(e) {
        e.preventDefault();

        if ($(this).hasClass('hashcash-disabled')) {
            return;
        }

        var $form = $("#quickkey");

        $.ajax({
            url: 'https://hashcash.io/api/quickkeys',
            dataType: 'jsonp',
            data: {
                hashcashid: $form.find("[name=hashcashid]").val()
            },
            success: function(data) {
                if (data.status && data.status == 'error') {
                    $("#hashcash_public_key", $form).val(data.message);
                    return;
                }

                $("#hashcash_public_key").val(data.publicKey);
                $("#hashcash_private_key").val(data.key);

                $(".hashcash-quickkey").fadeOut(500, function() {
                    $(".hashcash-quickkey").hide();
                });
            }
        });
    });
})(jQuery);
