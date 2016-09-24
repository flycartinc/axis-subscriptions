<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Axis Subscriptions
 * Plugin URI:        http://flycart.org/
 * Description:       A plugin for subscription management.
 * Version:           0.8.0
 * Author:            Flycart
 * Author URI:        http://flycart.org/
 * License:           GPLv3
 */

use Events\Event;
use Axisubs\Helper\SubscriptionMails;
use Axisubs\Controllers\Controller;
//dd(get_option('rewrite_rules'));

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/getherbert/framework/bootstrap/autoload.php';

Event::listen('mailPaymentCanceled', 'sendMailPaymentCanceled', '1');
Event::listen('mailSubscriptionExpired', 'sendMailSubscriptionExpired', '1');
Event::listen('mailPaymentCompleted', 'sendMailPaymentCompleted', '1');
Event::listen('mailPaymentFailed', 'sendMailPaymentFailed', '1');
Event::listen('mailSubscriptionActive', 'sendMailSubscriptionActive', '1');
Event::listen('mailPaymentPending', 'sendMailPaymentPending', '1');

Event::listen('query_vars', 'axisubsRewriteQueryVars', '1', 'filter');
function axisubsRewriteQueryVars( $vars )
{
    $vars[] = 'task';
    $vars[] = 'id';
    $vars[] = 'sid';
    $vars[] = 'slug';
    return $vars;
}

//For login users
Event::listen('wp_ajax_axisubs_ajax', 'ajaxRequestAxisubs', '1');
Event::listen('wp_ajax_axisubs_ajax_admin', 'ajaxRequestAxisubsAdmin', '1');
//For non login user trigger
Event::listen('wp_ajax_nopriv_axisubs_ajax', 'ajaxRequestAxisubs', '1');

/**
 * For ajax site request
 * */
function ajaxRequestAxisubs(){
    $controller = new Controller();
    $controller->ajaxCallSite();
    wp_die();
}

/**
 * For ajax admin request
 * */
function ajaxRequestAxisubsAdmin(){
    $controller = new Controller();
    $controller->ajaxCall();
    wp_die();
}

/**
 * For Rewrite URL
 * */
Event::listen('init', 'rewriteAxisURL', '1');
function rewriteAxisURL(){
    $permalinkPlain = get_option('permalink_structure')? 0: 1;
    global $wp_rewrite;
    flush_rewrite_rules();

    $plans_structure = 'axisplan/%plan%';
    add_rewrite_tag("%plan%", '([^/]+)', "axisubs_plan=");
    add_permastruct('axisubs_single_plan', $plans_structure, false);

    add_rewrite_tag("%task%", '([^/]+)', "task=");
    add_rewrite_tag("%slug%", '([^/]+)', "slug=");
    add_rewrite_tag("%id%", '([^/]+)', "id=");

    $plan_structure = 'axisplan/%plan%/%task%/%slug%/%id%';
    add_permastruct('axisubs_single_plan', $plan_structure, false);



    $subscriptions_structure = 'axisubs/%subscribe%';
    add_rewrite_tag("%subscribe%", '([^/]+)', "axisubs_subscribes=");
    add_permastruct('axisubs_single_subscribe', $subscriptions_structure, false);

    add_rewrite_tag("%sid%", '([^/]+)', "sid=");

    $subscription_structure = 'axisubs/%subscribe%/%task%/%sid%';
    add_permastruct('axisubs_single_subscribe', $subscription_structure, false);




//    add_rewrite_tag("%plan%", '([^/]+)/([^/]+)/([^/]+)/([^/]+)', "axisubs_plan=");
//    add_rewrite_rule(
//        'plans(/([^/]+))?(/([^/]+))?(/([^/]+))?/?',
//        'index.php?axisubs_plan=plans&task=$matches[2]&view=$matches[4]&id=$matches[6]',
//        'top'
//    );

//    add_rewrite_tag("%subscribe%", '([^/]+)', "axisubs_subscribes=");
//    add_permastruct('axisubs_single_subscribe', $storepress_structure, false);

//    $option = get_option('rewrite_rules');
//    echo "<pre>";
//    print_r($option);exit;
}

/**
 * sendMailPaymentCanceled
 * */
function sendMailPaymentCanceled($subscription_id){
    SubscriptionMails::subscriptionPaymentCanceled($subscription_id);
}

/**
 * sendMailSubscriptionExpired
 * */
function sendMailSubscriptionExpired($subscription_id){
    SubscriptionMails::subscriptionExpired($subscription_id);
}

/**
 * sendMailPaymentCompleted
 * */
function sendMailPaymentCompleted($subscription_id){
    SubscriptionMails::subscriptionPaymentCompleted($subscription_id);
}

/**
 * sendMailPaymentCompleted
 * */
function sendMailPaymentFailed($subscription_id){
    SubscriptionMails::subscriptionPaymentFailed($subscription_id);
}

/**
 * sendMailPaymentCompleted
 * */
function sendMailSubscriptionActive($subscription_id){
    SubscriptionMails::subscriptionActive($subscription_id);
}

/**
 * sendMailPaymentCompleted
 * */
function sendMailPaymentPending($subscription_id){
    SubscriptionMails::subscriptionPending($subscription_id);
}




function filter_single_plan_display($content)
{
    global $post;
    if ($post->post_type == 'axisubs_plan') {
        if ($post->ID) {
            echo do_shortcode("[AxisubsAllPlans post_id=" . $post->ID . "]");
            return;
        }
    }
    return $content;
}

function axisubs_single_plan_template($single_template)
{
    global $post;
    if ($post->post_type == 'axisubs_plan') {
        $single_template = plugin_dir_path(__FILE__) . 'templates/axisubs-single-plan-template.php';
    }

    return $single_template;
}

function filter_single_subscribe_display($content)
{
    global $post;
    if ($post->post_type == 'axisubs_subscribes') {
        if ($post->ID) {
            echo do_shortcode("[AxisubsAllSubscriptions post_id=" . $post->ID . "]");
            return;
        }
    }
    return $content;
}

function axisubs_single_subscribe_template($single_template)
{
    global $post;
    if ($post->post_type == 'axisubs_subscribes') {
        $single_template = plugin_dir_path(__FILE__) . 'templates/axisubs-single-subscribe-template.php';
    }

    return $single_template;
}

//Event::listen('single_template', 'axisubs_single_plan_template', '', 'filter');
//Event::listen('single_template', 'axisubs_single_subscribe_template', '', 'filter');
add_filter('single_template', 'axisubs_single_plan_template', '');
add_filter('single_template', 'axisubs_single_subscribe_template', '');

add_action('axisubs_single_plan', 'filter_single_plan_display', '');
add_action('axisubs_single_subscribe', 'filter_single_subscribe_display', '');

