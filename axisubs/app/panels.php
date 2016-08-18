<?php namespace Axisubs;

/** @var \Herbert\Framework\Panel $panel */
// For Main menu
$panel->add([
    'type'   => 'panel',
    'as'     => 'plans',
    'title'  => 'Axisubs',
    'slug'   => 'plans-index',
    'uses'   => __NAMESPACE__ . '\Controllers\AdminController@index'
]);

// For configuration menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'plans',
    'as'     => 'configuration',
    'title'  => 'Configuration',
    'slug'   => 'config-index',
    'uses'   => __NAMESPACE__ . '\Controllers\ConfigController@index'
]);

// For App menu
$panel->add([
    'type'   => 'sub-panel',
    'parent' => 'plans',
    'as'     => 'app',
    'title'  => 'Apps',
    'slug'   => 'app-index',
    'uses'   => __NAMESPACE__ . '\Controllers\AppController@index'
]);