(function ($) {
    $('.contact-form').on("submit", function () {
        $("input[type=text],input[type=email],input[type=url],textarea", $(this)).filter("[required]").each(function () {
            if (!$(this).val()) {
                return false;
            }
        })
        if ($("input[type=radio][name*='-sex']:checked", $(this)).length == 0)
            return false;
    });
})(jQuery);