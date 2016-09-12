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
            view: "Plan",
            task: "loadPlanFields"
        };
        $.ajax({
            url: $('#site_url').val()+'/index.php/axisubs-admin-ajax',
            type: 'POST',
            data: postData,
            beforeSend: function () {
                $('.axisubs-fields-plantypes-con-o .loader-ajax').show();
            },
            complete: function () {
                $('.axisubs-fields-plantypes-con-o .loader-ajax').hide();
            },
            success: function (data) {
                $('div.axisubs-fields-plantypes-c').html(data);
                checkForeverIsChoosen();
            }
        });
    })(axisubs.jQuery);
}

// For validationg plan
function validatePlan(){
    var valid = true;
    valid = validateRequiredFields('#planForm');
    return valid;
}

// for adding new customer
function addCustomer(){
    (function ($) {
        var messageText = $('.axisubs-user-message-text');
        messageText.hide();
        var valid = true;
        valid = validateRequiredFields('#my_profile');
        if(($('#axisubs_add_type').val() == '0') && ($('#axisubs_user_password1').val() != $('#axisubs_user_password2').val())){
            $('#axisubs_user_password1').addClass('invalid-field').focus();
            $('#axisubs_user_password2').addClass('invalid-field');
            valid = false;
        }
        if(valid){
            var fields = $("#my_profile").serializeArray();
            fields.push({'name':'task','value': 'addCustomer'});
            fields.push({'name':'view','value': 'Customer'});
            $.ajax({
                type: 'post',
                url: $('#site_url').val()+'/index.php/axisubs-admin-ajax',
                dataType: 'json',
                data: fields,
                cache: false,
                success: function (json) {
                    removeMessageClass('.axisubs-user-message-text');
                    messageText.html(json['message']).show();
                    if (json['status'] == 'success') {
                        messageText.addClass('message-success');
                        //$("#register_user").submit();
                        var redirectURL = $('#site_url').val()+'/wp-admin/admin.php?page=customers-index&task=edit&id='+json['ID'];
                        window.location = redirectURL;
                    } else {
                        messageText.addClass('message-danger');
                        if(json['field'] != undefined){
                            $('#axisubs_subscribe_'+json['field']).addClass('invalid-field').focus();
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    })(axisubs.jQuery);
}

// Customer new layout: On change customer type new/existing
function addCustomertype(val){
    (function ($) {
        if(val == '1'){
            $('.axisubs_customer_add_existing').show('slow');
            $('.axisubs_customer_add_new').hide('slow');
            $('input[type="password"]').removeClass('required');
        } else {
            $('.axisubs_customer_add_existing').hide('slow');
            $('.axisubs_customer_add_new').show('slow');
            $('input[type="password"]').addClass('required');
            $('#axisubs_wp_user_id').val('').trigger('change');
        }
    })(axisubs.jQuery);
}

// For auto populate plan fields for create subscription
function autoPopulatePlanDetails(val){
    (function ($) {
        var postData = {
            id: val,
            task: "loadPlanDetails",
            view: "Subscription"
        };
        $.ajax({
            url: $('#site_url').val()+'/index.php/axisubs-admin-ajax',
            type: 'POST',
            data: postData,
            dataType: 'json',
            beforeSend: function () {
                // $('.axisubs-fields-plantypes-con-o .loader-ajax').show();
            },
            complete: function () {
                // $('.axisubs-fields-plantypes-con-o .loader-ajax').hide();
            },
            success: function (data) {
                if(data['status'] == 'success'){
                    poupulatePlanData(data['fields']);
                } else {
                    poupulatePlanData();
                }

            }
        });

        function poupulatePlanData(data){
            $('.autopopulate_fields input').each(function (e) {
                $(this).val('');
            });
            if(data != undefined){
                if(data.price != '')
                    $('#subscribe_price').val(data.price);
            } else {

            }
        }
    })(axisubs.jQuery);
}

// Add new subscription
function addNewSubscription(){
    (function ($) {
        var valid = validateRequiredFields("#subscriptionForm");
        if(valid){
            $('#subscriptionForm').submit();
        } else {
            
        }
        var fields = $("#subscriptionForm").serializeArray();
        /*fields.push({'name':'id','value':val});
        fields.push({'name':'task','value': 'addNewSubscription'});
        fields.push({'name':'view','value': 'Subscription'});
        $.ajax({
            url: $('#site_url').val()+'/index.php/axisubs-admin-ajax',
            type: 'POST',
            data: fields,
            dataType: 'json',
            beforeSend: function () {
                // $('.axisubs-fields-plantypes-con-o .loader-ajax').show();
            },
            complete: function () {
                // $('.axisubs-fields-plantypes-con-o .loader-ajax').hide();
            },
            success: function (data) {
                if(data['status'] == 'success'){
                    poupulateCustomerData(data['fields']);
                } else {
                    poupulateCustomerData();
                }

            }
        });*/
    })(axisubs.jQuery);
}

// For auto populate fields
function autoPopulateCustomerDetails(val){
    (function ($) {
        var postData = {
            id: val,
            task: "loadCustomerDetails",
            view: "Customer"
        };
        $.ajax({
            url: $('#site_url').val()+'/index.php/axisubs-admin-ajax',
            type: 'POST',
            data: postData,
            dataType: 'json',
            beforeSend: function () {
               // $('.axisubs-fields-plantypes-con-o .loader-ajax').show();
            },
            complete: function () {
               // $('.axisubs-fields-plantypes-con-o .loader-ajax').hide();
            },
            success: function (data) {
                if(data['status'] == 'success'){
                    poupulateCustomerData(data['fields']);
                } else {
                    poupulateCustomerData();
                }

            }
        });

        function poupulateCustomerData(data){
            if(data != undefined){
                $('#axisubs-wordpress_username_text').html(data['user_login']);
                $('#axisubs_subscribe_first_name').val(data['first_name']);
                $('#axisubs_subscribe_last_name').val(data['last_name']);
                $('#axisubs_subscribe_email').val(data['email']);
                $('input[name="id"]').val(data['id']);
            } else {
                $('#axisubs-wordpress_username_text').html('');
                $('#axisubs_subscribe_first_name').val('');
                $('#axisubs_subscribe_last_name').val('');
                $('#axisubs_subscribe_email').val('');
                $('input[name="id"]').val('');
            }
        }
    })(axisubs.jQuery);
}

//for forever choosen
function checkForeverIsChoosen(){
    (function ($) {
        if($('#axisub_plan_period_forever').is(":checked")) {
            $('#axisub_plan_period').hide();
        } else {
            $('#axisub_plan_period').show();
        }
    })(axisubs.jQuery);
}

// For
(function ($) {
    $(document).ready(function () {
        // For on click event forever checkbox
        $(document).on('click', '#axisub_plan_period_forever', function() {
            checkForeverIsChoosen();
        });

        //For generating slug
        $(document).on( "blur", '#axisub_plan_name', function() {
            var name = $(this).val();
            name = name.toLowerCase(); // lowercase
            name = name.replace(/ +(?= )/g,'');
            name = name.replace(/\s/g,"-");
            name = name.replace(/[^a-z-0-9]/g, ''); // remove everything that is not [a-z] or -
            $('#axisub_plan_slug').val(name);
        });

        //for loading subscriptions
        $('.load_more_subscriptions').mouseout(function() {
            var contentDiv = $(this).children('.more_subscriptions-data');
            contentDiv.hide();
            contentDiv.prev('.more_subscriptions-data-left-arrow').hide();
        }).mouseover(function() {
            var selected = $(this);
            var postData = {
                id: jQuery(this).attr('data-attr'),
                task: "loadCustomerSubscriptions",
                view: "Customer"
            };
            var contentDiv = selected.children('.more_subscriptions-data');
            //selected.find('.more_subscriptions-data').show();
            contentDiv.show();
            contentDiv.prev('.more_subscriptions-data-left-arrow').show();
            if($(this).attr('send-attr') == '1'){
                return;
            } else {
                selected.parent().find('.more_subscriptions-data').html("Loading..");
                selected.attr('send-attr', '1');
            }
            $.ajax({
                url: $('#site_url').val()+'/index.php/axisubs-admin-ajax',
                type: 'POST',
                data: postData,
                async	: false,
                success: function(json) {
                    selected.parent().find('.more_subscriptions-data').html(json);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });
    });
})(axisubs.jQuery);