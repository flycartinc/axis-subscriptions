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

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/getherbert/framework/bootstrap/autoload.php';

Event::listen('mailPaymentCanceled', 'sendMailPaymentCanceled', '1');
Event::listen('mailSubscriptionExpired', 'sendMailSubscriptionExpired', '1');
Event::listen('mailPaymentCompleted', 'sendMailPaymentCompleted', '1');
Event::listen('mailPaymentFailed', 'sendMailPaymentFailed', '1');
Event::listen('mailSubscriptionActive', 'sendMailSubscriptionActive', '1');
Event::listen('mailPaymentPending', 'sendMailPaymentPending', '1');

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

add_filter('single_template', 'axisubs_single_plan_template', '');
add_filter('single_template', 'axisubs_single_subscribe_template', '');
add_action('axisubs_single_plan', 'filter_single_plan_display', '');

add_action('axisubs_single_subscribe', 'filter_single_subscribe_display', '');

