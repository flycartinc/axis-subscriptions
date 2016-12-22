<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Axisubs App Paypal
 * Plugin URI:        http://flycart.org/
 * Description:       A plugin for paypal.
 * Version:           1.0.0
 * Author:            Ashlin
 * Author URI:        http://flycart.org/
 * License:           MIT
 */

use Events\Event;
use AxisubsAppPaypal\Controllers\Admin\AxisubsAppPaypal;
use Herbert\Framework\Http;

if(!file_exists(WP_PLUGIN_DIR.'/axis-subscriptions/plugin.php')){
    return false;
}
/**
 * Include vendor
 * */
require_once( WP_PLUGIN_DIR.'/axis-subscriptions/vendor/autoload.php');
require_once( WP_PLUGIN_DIR.'/axis-subscriptions/vendor/getherbert/framework/bootstrap/autoload.php');

require_once( WP_PLUGIN_DIR.'/axisubs-app-payment-paypal/app/Controllers/Controller.php');
require_once( WP_PLUGIN_DIR.'/axisubs-app-payment-paypal/app/Models/Admin/AxisubsAppPaypal.php');
require_once( WP_PLUGIN_DIR.'/axisubs-app-payment-paypal/app/Controllers/Admin/AxisubsAppPaypal.php');

if (!defined('AXISUBS_APP_PAYPAL_PLUGIN_PATH')) {
    define( 'AXISUBS_APP_PAYPAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

Event::listen('axisubs-app-payment-paypal_loadView', array('AxisubsAppPaypalPlugin', 'loadFormView'), '1');
Event::listen('axisubs-app-payment-paypal_appTask', array('AxisubsAppPaypalPlugin', 'runAppTask'), '1');
Event::listen('axisubs-app-payment-paypal_paymentOptionRadio', array('AxisubsAppPaypalPlugin', 'paymentRadioButton'), '1');
Event::listen('axisubs-app-payment-paypal_paymentForm', array('AxisubsAppPaypalPlugin', 'loadPaymentForm'), '1');
Event::listen('axisubs-app-payment-paypal_paymentTask', array('AxisubsAppPaypalPlugin', 'processPaymentTasks'), '1');

Event::listen('axisubs-app-payment-paypal_hasFunction', array('AxisubsAppPaypalPlugin', 'hasHook'), '1');

class AxisubsAppPaypalPlugin
{
    protected static $hooks = array('loadView');

    /**
     * To check function exist
     * */
    public static function hasHook($functionName){
        if(in_array($functionName, AxisubsAppPaypalPlugin::$hooks)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * For load Form/View form
     * */
    public static function loadFormView(){
        AxisubsAppPaypal::loadAppView();
    }

    /**
     * Run app task
     * */
    public static function runAppTask(){
        $http = Http::capture();
        $paypalObject = new AxisubsAppPaypal();
        $paypalObject->execute($http);
    }

    /**
     * Load payment radio button
     * */
    public static function paymentRadioButton(){
        $html = '<div class="axisubs_payment_each_block">';
        $html .='<input type="radio" id="axisubs_payment_paypal" name="payment" value="axisubs-app-payment-paypal" />';
        $html .= '<label for="axisubs_payment_paypal">Paypal</label>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Load payment Form
     * */
    public static function loadPaymentForm($args){
        $subscription = $args[0];
        $plans = $args[1];
        $paypalObject = new AxisubsAppPaypal();
        return $paypalObject->loadPaymentForm($subscription, $plans);
    }

    /**
     * Run payment tasks
     * */
    public static function processPaymentTasks($args){
        $paypalObject = new AxisubsAppPaypal();
        return $paypalObject->processPayment();
    }
}
