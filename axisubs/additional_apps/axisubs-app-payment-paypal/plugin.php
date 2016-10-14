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
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/getherbert/framework/bootstrap/autoload.php';

if (!defined('AXISUBS_APP_PAYPAL_PLUGIN_PATH')) {
    define( 'AXISUBS_APP_PAYPAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

Event::listen('axisubs-app-payment-paypal_loadView', 'loadFormView', '1');
Event::listen('axisubs-app-payment-paypal_appTask', 'runAppTask', '1');
Event::listen('axisubs-app-payment-paypal_paymentOptionRadio', 'paymentRadioButton', '1');
Event::listen('axisubs-app-payment-paypal_paymentForm', 'loadPaymentForm', '1');
Event::listen('axisubs-app-payment-paypal_paymentTask', 'processPaymentTasks', '1');

/**
 * For load Form/View form
 * */
function loadFormView(){
    AxisubsAppPaypal::loadAppView();
}

/**
 * Run app task
 * */
function runAppTask(){
    $http = Http::capture();
    $paypalObject = new AxisubsAppPaypal();
    $paypalObject->execute($http);
}

/**
 * Load payment radio button
 * */
function paymentRadioButton(){
    $html = '<div class="axisubs_payment_each_block">';
    $html .='<input type="radio" id="axisubs_payment_paypal" name="payment" value="axisubs-app-payment-paypal" />';
    $html .= '<label for="axisubs_payment_paypal">Paypal</label>';
    $html .= '</div>';
    return $html;
}

/**
 * Load payment Form
 * */
function loadPaymentForm($args){
    $subscription = $args[0];
    $plans = $args[1];
    $paypalObject = new AxisubsAppPaypal();
    return $paypalObject->loadPaymentForm($subscription, $plans);
}

/**
 * Run payment tasks
 * */
function processPaymentTasks($args){
    $paypalObject = new AxisubsAppPaypal();
    return $paypalObject->processPayment();
}