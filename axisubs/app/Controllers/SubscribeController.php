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

class SubscribeController{
    //Show all Plans
    public function showAllSubscriptions(Http $http){
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        $pagetitle = "Subscriptions";
        $subscribtions_url = get_site_url().'/index.php?axisubs_subscribes=subscribe';
        $subscribers = Plans::loadAllSubscribes();
        return view('@Axisubs/Site/subscribed/list.twig', compact('pagetitle', 'subscribtions_url', 'subscribers', 'currencyData', 'site_url'));
    }

    public function showSelectedPlan(){
        //echo 12312312323;
        //echo 123123;
       // echo '<pre>';print_r($plan_slug);echo '</pre>';
//       $pagetitle = "Plans";
//        $items = Plans::allFrontEnd();
//        $currency = new Currency();
//        $currencyData['code'] = $currency->getCurrencyCode();
//        $currencyData['currency'] = $currency->getCurrency();
//        return view('@Axisubs/Site/plans/list.twig', compact('pagetitle','items', 'currencyData'));
//        return view('@Axisubs/Site/subscribe/details.twig', compact('pagetitle','items', 'currencyData'));

        return view('@Axisubs/Site/subscribe/details.twig');
    }
}