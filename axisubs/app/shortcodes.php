<?php namespace Axisubs;

/** @var \Herbert\Framework\Shortcode $shortcode */

//For displaying plans
$shortcode->add(
    'AxisubsAllPlans',
    __NAMESPACE__ . '\Controllers\Site\Plan@showAllPlans',
    [
        'post_id' => 'postId'
    ]
);

//For displaying Subscriptions
$shortcode->add(
    'AxisubsAllSubscriptions',
    __NAMESPACE__ . '\Controllers\Site\Subscribe@showAllSubscriptions',
    [
        'post_id' => 'postId'
    ]
);

//For displaying Profile View
$shortcode->add(
    'AxisubsMyProfile',
    __NAMESPACE__ . '\Controllers\Site\Profile@showMyProfile',
    [
        'post_id' => 'postId'
    ]
);