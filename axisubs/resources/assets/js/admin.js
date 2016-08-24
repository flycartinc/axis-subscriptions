if (typeof(axisubs) == 'undefined') {
    var axisubs = {};
}
if (typeof(axisubs.jQuery) == 'undefined') {
    axisubs.jQuery = jQuery.noConflict();
}

/* For loading fields based on plan type */
function loadFieldsOfPlanType(val, id) {
    (function ($) {
        var postData = {
            id: id,
            type: val,
            task: "loadPlanFields"
        };
        $.ajax({
            url: 'http://localhost/wordpress/axisubs/dev/index.php/axisubs-admin-ajax',
            type: 'POST',
            data: postData,
            beforeSend: function () {
                $('.axisubs-fields-plantypes-con-o .loader-ajax').show();
            },
            complete: function () {
                $('.axisubs-fields-plantypes-con-o .loader-ajax').hide();
            },
            success: function (json) {
                $('div.axisubs-fields-plantypes-c').html(json);
            }
        });
    })(axisubs.jQuery);
}

// For
(function ($) {
    $(document).ready(function () {
    });
})(axisubs.jQuery);