(function($) {
    window.Vrok = window.Vrok || {};

    /**
     * Usability helper for password inputs. Asks the server to rate the
     * currently entered password and gives a visual response to how secure
     * it is.
     *
     * @param {Node} element   password input
     * @return {void}
     */
    Vrok.ratePassword = function(element) {
        var data = {
            pw: $(element).val()
        };

        if (!data.pw) {
            $(element).siblings('.password-rating').remove();
            return;
        }

        var request = {
            type: 'POST',
            dataType: 'json',
            data: data,
            url: '<?php echo $this->fullUrl().$this->url("user/password-strength"); ?>',
            success: function (data) {
                $(element).parent('.form-group')
                        .removeClass('has-success has-warning has-error');

                if (data.rating === 'bad' || data.rating === 'weak') {
                    $(element).parent('.form-group').addClass('has-error');
                } else if (data.rating === 'ok') {
                    $(element).parent('.form-group').addClass('has-warning');
                } else {
                    $(element).parent('.form-group').addClass('has-success');
                }
            },
            error: function (data) {
                console.error('Vrok.ratePassword: Request to failed!');

                // still try to process, maybe we received a 403 with a
                // redirect in the response.script
                Vrok.Tools.processResponse(data.responseJSON, $container, defaults);
            }
        };

        $.ajax(request);
    };

    // initialize forms on page load
    $(document).ready(function() {
        $("body").on('keyup', 'input.rate-password', function(e) {
            clearTimeout(Vrok.ratePassword.timer);

            Vrok.ratePassword.timer = setTimeout(function() {
                Vrok.ratePassword(e.currentTarget);
            }, 300);
        });

        // get users consent to using cookies on this website
        $.cookieBar({
            message: "<?php echo $this->translate('cookiebar.message'); ?>",
            acceptText: "<?php echo $this->translate('cookiebar.acceptText'); ?>",
            append: true,
            bottom: true,
            fixed: true,
            policyButton: true,
            policyText: "<?php echo $this->translate('cookiebar.policyText'); ?>",
            policyURL: "<?php echo $this->url('privacy'); ?>#cookies"
        });
    });
}(jQuery));
