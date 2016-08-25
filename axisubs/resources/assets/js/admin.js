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

function validatePlan(){
    var valid = true;
    axisubs.jQuery( "#planForm .required" ).removeClass('invalid-field');
    var firstField = 0;
    axisubs.jQuery( "#planForm .required" ).each(function( index ) {
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

        // $( "#planForm" ).submit(function( event ) {
        //     var result = validatePlan();
        //     if(result){
        //         $(this).submit();
        //     }
        // });
    });
})(axisubs.jQuery);