<?php namespace Axisubs;

/** @var \Herbert\Framework\Enqueue $enqueue */
class AxisubsEnqueue
{
    /**
     * For loading style and script
     * */
    public static function axisubsEnqueueFiles()
    {
        if(is_admin()){
            AxisubsEnqueue::addAdminStyle();
        } else {
            AxisubsEnqueue::addSiteStyle();
        }
    }

    /**
     * For loading admin style and script
     * */
    public static function addAdminStyle(){
        wp_enqueue_style( 'axisubs_admin_style', Helper::assetUrl('/css/admin.css') );
        wp_enqueue_style( 'axisubs_bootstrap_style', Helper::assetUrl('/css/axisubs_bootstrap.min.css') );
        wp_enqueue_style( 'axisubs_jquery_style', Helper::assetUrl('/css/jquery-ui.min.css') );

        wp_enqueue_script("jquery");
        wp_enqueue_script('jquery-ui-datepicker');
        //wp_enqueue_script( 'axisubs_jquery_script', Helper::assetUrl('/js/jquery-3.1.0.min.js') );
        wp_enqueue_script( 'axisubs_circliful_script', Helper::assetUrl('/js/jquery.circliful.min.js') );
        wp_enqueue_script( 'axisubs_admin_script', Helper::assetUrl('/js/admin.js') );
        wp_enqueue_script( 'axisubs_common_script', Helper::assetUrl('/js/common.js') );

    }

    /**
     * For loading site style and script
     * */
    public static function addSiteStyle(){
        wp_enqueue_style( 'axisubs_site_style', Helper::assetUrl('/css/style.css') );

        wp_enqueue_script("jquery");
        wp_enqueue_script('jquery-ui-datepicker');
        //wp_enqueue_script( 'axisubs_jquery_script', Helper::assetUrl('/js/jquery-3.1.0.min.js') );
        wp_enqueue_script( 'axisubs_site_script', Helper::assetUrl('/js/site.js') );
        wp_enqueue_script( 'axisubs_common_script', Helper::assetUrl('/js/common.js') );
    }
}