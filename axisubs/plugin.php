<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Axisubs
 * Plugin URI:        http://flycart.org/
 * Description:       A plugin for subscription management.
 * Version:           1.0.0
 * Author:            Ashlin
 * Author URI:        http://flycart.org/
 * License:           MIT
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/getherbert/framework/bootstrap/autoload.php';


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


http://localhost/wordpress/axisubs/dev/wp-admin/admin.php?page=plans-index&task=edit&id=5
