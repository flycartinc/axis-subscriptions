if (typeof(axisubs) == 'undefined') {
    var axisubs = {};
}
if (typeof(axisubs.jQuery) == 'undefined') {
    axisubs.jQuery = jQuery.noConflict();
}

//For login a user
function loginUser(){
    (function ($) {
        $('.axisubs-login-message-text').hide();
        var valid = validateRequiredFields("#login_user");
        if(valid) {
            var fields = $("#login_user").serializeArray();
            //fields.push({'name':'ajax','value':'ajax'});
            $.ajax({
                type: 'post',
                url: $('#site_url').val()+'/index.php/axisubs-site-login',
                dataType: 'json',
                data: fields,
                cache: false,
                async: false,
                success: function (json) {
                    removeMessageClass('.axisubs-login-message-text');
                    $('.axisubs-login-message-text').html(json['message']).show();
                    if (json['status'] == 'success') {
                        $('.axisubs-login-message-text').addClass('message-success');
                        location.reload();
                    } else {
                        $('.axisubs-login-message-text').addClass('message-danger');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    })(axisubs.jQuery);
}

//for registering User
function registerUser(){
    (function ($) {
        var meesageText = $('.axisubs-register-message-text');
        meesageText.hide();
        var valid = validateRequiredFields("#register_user");
        if($('#axisubs_user_password1').val() != $('#axisubs_user_password2').val()){
            $('#axisubs_user_password1').addClass('invalid-field').focus();
            $('#axisubs_user_password2').addClass('invalid-field');
            valid = false;
        }
        if(valid) {
            var fields = $("#register_user").serializeArray();
            //fields.push({'name':'ajax','value':'ajax'});
            $.ajax({
                type: 'post',
                url: $('#site_url').val()+'/index.php/axisubs-site-registeruser',
                dataType: 'json',
                data: fields,
                cache: false,
                async: false,
                success: function (json) {
                    removeMessageClass('.axisubs-register-message-text');
                    meesageText.html(json['message']).show();
                    if (json['status'] == 'success') {
                        meesageText.addClass('message-success');
                        $("#register_user").submit();
                    } else {
                        meesageText.addClass('message-danger');
                    }
                    console.log(json)
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    })(axisubs.jQuery);
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

// For
(function ($) {
    $(document).ready(function () {
        // For on click event forever checkbox
        $(document).on('click', '.login_registration_tab_ul li', function() {
            $('.login_registration_tab_ul li').removeClass('active');
            $('.tab_con').hide();
            $(this).addClass('active');
            $('.'+$(this).attr('data')).show();
        });
    });
})(axisubs.jQuery);