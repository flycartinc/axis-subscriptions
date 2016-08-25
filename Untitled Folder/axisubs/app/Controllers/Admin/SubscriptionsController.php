<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Models\Admin\Subscriptions;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

use Axisubs\Helper\Status;
use Axisubs\Models\Plans;
use Axisubs\Helper\Currency;

class SubscriptionsController
{
    public function index(Http $http)
    {
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $role_names = wp_roles()->role_names;
        $pagetitle = 'Subscriptions';
        $site_url = get_site_url();
        if ($http->has('sid')) {
            $subscriber = Subscriptions::loadSubscriber($http->get('sid'));
            $planDetails = array();
            if(isset($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id'])){
                $planDetails = Subscriptions::loadPlan($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id']);
                $status = new Status();
                $statusCode = $subscriber->meta[$subscriber->ID.'_axisubs_subscribe_status'];
                $statusText = $status->getStatusText($statusCode);
            }
            return view('@Axisubs/Admin/subscriptions/detail.twig', compact('pagetitle', 'subscriber', 'currencyData', 'site_url', 'planDetails', 'statusText'));
        }
        // Load Listing layout
        $items = Subscriptions::all();
        return view('@Axisubs/Admin/subscriptions/list.twig', compact('pagetitle', 'items'));
    }
}