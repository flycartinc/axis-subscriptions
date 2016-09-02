<?php namespace Axisubs;

/** @var \Herbert\Framework\Router $router */

$router->get([
    'as' => 'postSingle',
    'uri' => 'post/{id}',
    'uses' => __NAMESPACE__ . '\Controllers\PostController@showPost'
]);

$router->get([
    'as' => 'subscribe',
    'uri' => '/subscribe/{plan_slug}',
    'uses' => __NAMESPACE__ . '\Controllers\Site\Plan@showSelectedPlan'
]);

$router->post([
    'as' => "axisubsAjax",
    'uri' => '/axisubs-admin-ajax',
    'uses' => __NAMESPACE__ . '\Controllers\Controller@ajaxCall'
]);

$router->post([
    'as' => "axisubsAjaxSiteAll",
    'uri' => '/axisubs-site-ajax',
    'uses' => __NAMESPACE__ . '\Controllers\Controller@ajaxCallSite'
]);