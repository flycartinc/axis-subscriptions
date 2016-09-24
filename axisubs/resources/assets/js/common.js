if (typeof(axisubs) == 'undefined') {
    var axisubs = {};
}
if (typeof(axisubs.jQuery) == 'undefined') {
    axisubs.jQuery = jQuery.noConflict();
}

//validate Required fields
function validateRequiredFields(id){
    var valid = true;
    axisubs.jQuery( id+" .required" ).removeClass('invalid-field');
    var firstField = 0;
    axisubs.jQuery( id+" .required" ).each(function( index ) {
        if(axisubs.jQuery( this ).val() == ''){
            firstField++;
            axisubs.jQuery( this ).addClass('invalid-field');
            if(firstField == 1){
                axisubs.jQuery( this ).focus();
            }
            valid = false;
        }
    });
    return valid;
}

// remove message default class
function removeMessageClass(id){
    (function ($) {
        $(id).removeClass('message-success')
            .removeClass('message-danger')
        ;
    })(axisubs.jQuery);
}

function disableTags(id, val){
    if(val == '1'){
        axisubs.jQuery(id+' input, '+id+' select').attr('disabled', 'disabled');
    } else {
        axisubs.jQuery(id+' input, '+id+' select').removeAttr('disabled');
    }
}

(function ($) {
    $(document).ready(function () {
        $('.axisubs_country').change(function() {
            var postData = {
                code: jQuery(this).val(),
                task: "loadProvinces",
                view: "User",
                action: 'axisubs_ajax'
            };
            $.ajax({
                url: $('#site_url').val()+'/wp-admin/admin-ajax.php',
                type: 'POST',
                data: postData,
                async	: false,
                success: function(json) {
                    $('.axisubs_province').html(json);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });
    });
})(axisubs.jQuery);