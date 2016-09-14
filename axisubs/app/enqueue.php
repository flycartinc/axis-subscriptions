<?php namespace Axisubs;

/** @var \Herbert\Framework\Enqueue $enqueue */

/**
 * For Back end
*/
$enqueue->admin([
    'as'  => 'adminCSS',
    'src' => Helper::assetUrl('/css/admin.css'),
]);
$enqueue->admin([
    'as'  => 'adminBootstrap3',
    'src' => Helper::assetUrl('/css/axisubs_bootstrap.min.css'),
]);

$enqueue->admin([
    'as'     => 'adminJquery',
    'src'    => Helper::assetUrl('/js/jquery-3.1.0.min.js'),
    'filter' => [ 'panel' => '*' ]
]);
$enqueue->admin([
    'as'     => 'adminDashboard',
    'src'    => Helper::assetUrl('/js/jquery.circliful.min.js'),
    'filter' => [ 'panel' => 'dashboard-index' ]
]);
$enqueue->admin([
    'as'     => 'adminCommonJS',
    'src'    => Helper::assetUrl('/js/common.js'),
    'filter' => [ 'panel' => '*' ]
]);
$enqueue->admin([
    'as'     => 'adminJS',
    'src'    => Helper::assetUrl('/js/admin.js'),
    'filter' => [ 'panel' => '*' ]
]);

/**
 * For front end
 */
$enqueue->front([
    'as'  => 'frontCSS',
    'src' => Helper::assetUrl('/css/style.css'),
]);
$enqueue->front([
    'as'     => 'siteJquery',
    'src'    => Helper::assetUrl('/js/jquery-3.1.0.min.js')
]);
$enqueue->front([
    'as'     => 'siteCommonJS',
    'src'    => Helper::assetUrl('/js/common.js')
]);
$enqueue->front([
    'as'     => 'siteJS',
    'src'    => Helper::assetUrl('/js/site.js')
]);