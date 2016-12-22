<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Axis Subscription App Taxes
 * Plugin URI:        http://flycart.org/
 * Description:       A plugin for Axis-Subscriptions Taxes.
 * Version:           1.0.0
 * Author:            Ashlin
 * Author URI:        http://flycart.org/
 * License:           MIT
 */

use Events\Event;
use AxisubsAppTaxes\Controllers\Admin\AxisubsAppTaxes;
use Herbert\Framework\Http;
if(!file_exists(WP_PLUGIN_DIR.'/axis-subscriptions/plugin.php')){
    return false;
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !is_plugin_active( 'axis-subscriptions/plugin.php' ) ) {
    return false;
}

/**
 * Include vendor
 * */
require_once( WP_PLUGIN_DIR.'/axis-subscriptions/vendor/autoload.php');
require_once( WP_PLUGIN_DIR.'/axis-subscriptions/vendor/getherbert/framework/bootstrap/autoload.php');

require_once( WP_PLUGIN_DIR.'/axisubs-app-taxes/app/Controllers/Controller.php');
require_once( WP_PLUGIN_DIR.'/axisubs-app-taxes/app/Models/Admin/AxisubsAppTaxes.php');
require_once( WP_PLUGIN_DIR.'/axisubs-app-taxes/app/Models/Admin/AxisubsAppTaxRates.php');
require_once( WP_PLUGIN_DIR.'/axisubs-app-taxes/app/Controllers/Admin/AxisubsAppTaxes.php');
require_once( WP_PLUGIN_DIR.'/axisubs-app-taxes/app/Helper/Tax.php');

if (!defined('AXISUBS_APP_TAXES_PLUGIN_PATH')) {
    define( 'AXISUBS_APP_TAXES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

Event::listen('axisubs-app-taxes_loadView', array('AxisubsAppTaxesPlugin', 'loadFormView'), '1');
Event::listen('axisubs-app-taxes_appTask', array('AxisubsAppTaxesPlugin', 'runAppTask'), '1');

Event::listen('axisubs-app-taxes_ajax', array('AxisubsAppTaxesPlugin', 'runAppTask'), '1');
Event::listen('axisubs-app-additionalPrice', array('AxisubsAppTaxesPlugin', 'additionalPrice'), '10');
Event::listen('axisubs-app-validateSubscribeForm', array('AxisubsAppTaxesPlugin', 'validateSubscribeForm'), '10');
Event::listen('axisubs-app-taxes_loadContentInPriceList', array('AxisubsAppTaxesPlugin', 'loadContentInPriceList'), '9');
Event::listen('axisubs-app-taxes_hasFunction', array('AxisubsAppTaxesPlugin', 'hasHook'), '1');
Event::listen('axisubs-app-taxes_loadContentInSubscriptionPriceList', array('AxisubsAppTaxesPlugin', 'loadContentInSubscriptionPriceList'), '1');

class AxisubsAppTaxesPlugin
{
    protected static $hooks = array('loadView',
        'loadContentInPriceList',
        'loadContentInSubscriptionPriceList'
    );

    /**
     * To check function exist
     * */
    public static function hasHook($functionName){
        if(in_array($functionName, AxisubsAppTaxesPlugin::$hooks)){
            return 50;
        } else {
            return false;
        }
    }

    /**
     * For load Form/View form
     * */
    public static function loadFormView()
    {
        AxisubsAppTaxes::loadAppView();
    }

    /**
     * Run app task
     * */
    public static function runAppTask()
    {
        $http = Http::capture();
        $paypalObject = new AxisubsAppTaxes();
        $paypalObject->execute($http);
    }

    /**
     * Load content in price list
     * */
    public static function loadContentInPriceList($item){
        $plan = $item[0];
        $subscription = $item[2];
        $page = $item[3];
        if($plan->meta[$plan->ID.'_'.$plan->post_type.'_type'] == 'free'){
            return '';
        }
        return AxisubsAppTaxes::loadTaxDetails($plan, $subscription, $page);
    }

    /**
     * Load content in subscription price
     * */
    public static function loadContentInSubscriptionPriceList($item){
        $subscription = $item[0];
        $object = new AxisubsAppTaxes();
        return $object->loadTaxPriceInSubscriptionPage($subscription);
    }

    /**
     * Axjax call
     * */
    public static function ajaxCall(){
        $http = Http::capture();
        $object = new AxisubsAppTaxes();
        $appTask = $http->get('apptask', '');
        if($appTask != ''){
            $object->$appTask();
        } else {
            echo '';
        }
    }

    /**
     * adding / changing price based on discount
     * */
    public static function additionalPrice($item){        
        $taxObject = new AxisubsAppTaxes();
        $taxes = $taxObject->calculateTaxTotals($item);
        $tax_discount = $taxObject-> calculateTaxDiscounts($item);

    }

    /**
     * validateSubscribeForm
     * */
    public static function validateSubscribeForm($item){
        $error = &$item[0];
        $post = $item[1];
        $taxObject = new AxisubsAppTaxes();
        $taxObject->validateTaxNumber($error, $post);

    }
}
