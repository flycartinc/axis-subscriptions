<?php namespace Axisubs;

/** @var \Herbert\Framework\Enqueue $enqueue */

$enqueue->admin([
    'as'  => 'adminCSS',
    'src' => Helper::assetUrl('/css/admin.css'),
]);
$enqueue->front([
    'as'  => 'frontCSS',
    'src' => Helper::assetUrl('/css/style.css'),
]);

$enqueue->admin([
    'as'     => 'adminJquery',
    'src'    => Helper::assetUrl('/js/jquery-3.1.0.min.js'),
    'filter' => [ 'panel' => '*' ]
]);
$enqueue->admin([
    'as'     => 'adminJS',
    'src'    => Helper::assetUrl('/js/admin.js'),
    'filter' => [ 'panel' => '*' ]
]);