<?php namespace Axisubs;

use Axisubs\Helper\ExpiryControl;
/** @var \Herbert\Framework\API $api */

/**
 * Gives you access to the Helper class from Twig
 * {{ Axisubs.helper('assetUrl', 'icon.png') }}
 */
$api->add('helper', function ()
{
    $args = func_get_args();
    $method = array_shift($args);

    return forward_static_call_array(__NAMESPACE__ . '\\Helper::' . $method, $args);
});

(new ExpiryControl)->processSubscriptionExpiry();