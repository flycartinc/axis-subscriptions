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
            fields.push({'name':'view','value':'User'});
            fields.push({'name':'action','value':'axisubs_ajax'});
            $.ajax({
                type: 'post',
                url: $('#site_url').val()+'/wp-admin/admin-ajax.php',
                //url: $('#site_url').val()+'/index.php/axisubs-site-ajax',
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

function cancelProfileUpdate(){
    disableTags('#my_profile', '1');
    axisubs.jQuery('.axisubs-profile-view-button').show();
    axisubs.jQuery('.axisubs-profile-update-button').hide();
}
function editProfile(){
    disableTags('#my_profile', '0');
    axisubs.jQuery('.axisubs-profile-update-button').show();
    axisubs.jQuery('.axisubs-profile-view-button').hide();
}

//update user Details
function updateProfile(){
    (function ($) {
        var meesageText = $('.axisubs-profile-message-text');
        meesageText.hide();
        var valid = validateRequiredFields("#my_profile");
        if(valid) {
            var fields = $("#my_profile").serializeArray();
            fields.push({'name':'view','value':'User'});
            fields.push({'name':'task','value':'updateProfile'});
            fields.push({'name':'action','value':'axisubs_ajax'});
            $.ajax({
                type: 'post',
                url: $('#site_url').val()+'/wp-admin/admin-ajax.php',
                dataType: 'json',
                data: fields,
                cache: false,
                async: false,
                success: function (json) {
                    removeMessageClass('.axisubs-profile-message-text');
                    meesageText.html(json['message']).show();
                    if (json['status'] == 'success') {
                        meesageText.addClass('message-success');
                        location.reload();
                    } else {
                        meesageText.addClass('message-danger');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    })(axisubs.jQuery);
}

//validate payment radio button
function validateForPaymentOption(){
    axisubs.jQuery( "input[name=payment]" ).next().removeClass('invalid-field');
    if ( axisubs.jQuery( "input[name=payment]" ).length ) {
        if(axisubs.jQuery( "input[name=payment]" ).is(':checked')){
            return true;
        } else {
            axisubs.jQuery( "input[name=payment]" ).next().addClass('invalid-field');
            return false;
        }
    } else {
        return true;
    }
}

//Validate subscription
function submitSubscriptions(){
    (function ($) {
        var valid = validateRequiredFields("#register_user");
        if(valid){
            valid = validateForPaymentOption();
        }
        if(valid){
            //axisubs.jQuery("#register_user").submit();
            var meesageText = $('.axisubs-register-message-text');
            meesageText.hide();
            var fields = $("#register_user").serializeArray();
            fields.push({'name':'view','value':'Plan'});
            fields.push({'name':'task','value':'save'});
            fields.push({'name':'action','value':'axisubs_ajax'});
            meesageText.html('Loading please wait..').show();
            meesageText.addClass('message-success');
            $.ajax({
                type: 'post',
                url: $('#site_url').val()+'/wp-admin/admin-ajax.php',
                dataType: 'json',
                data: fields,
                cache: false,
                async: false,
                success: function (json) {
                    removeMessageClass('.axisubs-register-message-text');
                    meesageText.html(json['message']).show();
                    if (json['status'] == 'success') {
                        meesageText.addClass('message-success');
                        window.location.href = json['redirect'];
                    } else {
                        if(json['invalidfields'] != undefined){
                            $.each( json['invalidfields'], function( key, value ) {
                                $('#axisubs_subscribe_'+key).addClass('invalid-field')
                            });
                        }
                        meesageText.addClass('message-danger');
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
            fields.push({'name':'view','value':'User'});
            fields.push({'name':'task','value':'registerUser'});
            fields.push({'name':'action','value':'axisubs_ajax'});
            $.ajax({
                type: 'post',
                url: $('#site_url').val()+'/wp-admin/admin-ajax.php',
                dataType: 'json',
                data: fields,
                cache: false,
                async: false,
                success: function (json) {
                    removeMessageClass('.axisubs-register-message-text');
                    meesageText.html(json['message']).show();
                    if (json['status'] == 'success') {
                        meesageText.addClass('message-success');
                        //$("#register_user").submit();
                        submitSubscriptions();
                    } else {
                        meesageText.addClass('message-danger');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    })(axisubs.jQuery);
}

// Document ready functions
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