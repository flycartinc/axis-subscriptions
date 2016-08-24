<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers;

use Axisubs\Models\Plans;
use Axisubs\Helper\Currency;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper\Status;

class SubscribeController{
    //Show all Plans
    public function showAllSubscriptions(Http $http){
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        $pagetitle = "Subscriptions";
        $subscribtions_url = get_site_url().'/index.php?axisubs_subscribes=subscribe';
        if($http->get('sid')) {
            $subscriber = Plans::loadSubscriber($http->get('sid'));
            $planDetails = array();
            if(isset($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id'])){
                $planDetails = Plans::loadPlan($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id']);
                $status = new Status();
                $statusCode = $subscriber->meta[$subscriber->ID.'_axisubs_subscribe_status'];
                $statusText = $status->getStatusText($statusCode);
            }
            return view('@Axisubs/Site/subscribed/singlesubscription.twig', compact('pagetitle', 'subscribtions_url', 'subscriber', 'currencyData', 'site_url', 'planDetails', 'statusText'));
        } else {
            $subscribers = Plans::loadAllSubscribes();
            return view('@Axisubs/Site/subscribed/list.twig', compact('pagetitle', 'subscribtions_url', 'subscribers', 'currencyData', 'site_url'));
        }        
    }

    public function showSelectedPlan(){
        return view('@Axisubs/Site/subscribe/details.twig');
    }
}