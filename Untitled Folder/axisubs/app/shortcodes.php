<?php namespace Axisubs;

/** @var \Herbert\Framework\Shortcode $shortcode */

//For displaying plans
$shortcode->add(
    'AxisubsAllPlans',
    __NAMESPACE__ . '\Controllers\PlanController@showAllPlans',
    [
        'post_id' => 'postId'
    ]
);

//For displaying Subscriptions
$shortcode->add(
    'AxisubsAllSubscriptions',
    __NAMESPACE__ . '\Controllers\SubscribeController@showAllSubscriptions',
    [
        'post_id' => 'postId'
    ]
);

//For displaying Profile View
$shortcode->add(
    'AxisubsMyProfile',
    __NAMESPACE__ . '\Controllers\ProfileController@showMyProfile',
    [
        'post_id' => 'postId'
    ]
);