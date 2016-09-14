<?php namespace Axisubs;

/** @var \Herbert\Framework\Panel $panel */
// For Main menu
$panel->add([
    'type'   => 'panel',
    'as'     => 'dashboard',
    'title'  => 'Axisubs',
    'rename' => 'Dashboard',
    'slug'   => 'dashboard-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Dashboard@execute'
]);

// For Plans menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'dashboard',
    'as'     => 'plans',
    'title'  => 'Plans',
    'slug'   => 'plans-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Plan@execute'
]);

// For Subscriptions menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'dashboard',
    'as'     => 'subscriptions',
    'title'  => 'Subscriptions',
    'slug'   => 'subscriptions-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Subscription@execute'
]);

// For Subscriptions menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'dashboard',
    'as'     => 'customers',
    'title'  => 'Customers',
    'slug'   => 'customers-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Customer@execute'
]);

// For configuration menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'dashboard',
    'as'     => 'configuration',
    'title'  => 'Configuration',
    'slug'   => 'config-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\Config@execute'
]);

// For App menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'dashboard',
    'as'     => 'app',
    'title'  => 'Apps',
    'slug'   => 'app-index',
    'uses'   => __NAMESPACE__ . '\Controllers\Admin\App@execute'
]);