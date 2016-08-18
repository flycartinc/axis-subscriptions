<?php namespace Axisubs;

/** @var \Herbert\Framework\Shortcode $shortcode */

$shortcode->add(
    'AxisubsAllPlans',
    __NAMESPACE__ . '\Controllers\PlanController@showAllPlans',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
    'StorePressSingleProductTitle',
    __NAMESPACE__ . '\Controllers\PlanController@showAllPlan',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
    'StorePressSingleProduct',
    __NAMESPACE__ . '\Controllers\PlanController@showSelectedPlan',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
    'AxisubsAllSubscriptions',
    __NAMESPACE__ . '\Controllers\SubscribeController@showAllSubscriptions',
    [
        'post_id' => 'postId'
    ]
);