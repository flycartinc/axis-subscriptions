<?php namespace Axisubs;

/** @var \Herbert\Framework\Panel $panel */
// For Main menu
$panel->add([
    'type'   => 'panel',
    'as'     => 'plans',
    'title'  => 'Axisubs',
    'rename' => 'Plans',
    'slug'   => 'plans-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Plan@execute'
]);

// For Subscriptions menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'plans',
    'as'     => 'subscriptions',
    'title'  => 'Subscriptions',
    'slug'   => 'subscriptions-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Subscription@index'
]);

// For Subscriptions menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'plans',
    'as'     => 'customers',
    'title'  => 'Customers',
    'slug'   => 'customers-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Customer@index'
]);

// For configuration menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'plans',
    'as'     => 'configuration',
    'title'  => 'Configuration',
    'slug'   => 'config-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Config@index'
]);

// For App menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'plans',
    'as'     => 'app',
    'title'  => 'Apps',
    'slug'   => 'app-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\App@execute'
]);