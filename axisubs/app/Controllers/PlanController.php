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
use Herbert\Framework\Response;

class PlanController{
    //Show all Plans
    public function showAllPlans(Http $http){

        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        if($http->get('slug')=='') {
            $pagetitle = "Plans";
            $items = Plans::allFrontEndPlans();
            return view('@Axisubs/Site/plans/list.twig', compact('pagetitle','items', 'currencyData', 'site_url'));
        } else {
            $pagetitle = "Order Summary";
            $item = Plans::loadPlan($http->get('id'));
            $meta = $item->meta;
            //for adding subscriber
            if($http->get('task')=='save'){
                $result = Plans::addSubscribe($http->all(), $item);
                if($result) {
                    $subscriber = Plans::loadSubscriber($result);
                    return view('@Axisubs/Site/subscribe/subscribe.twig', compact('pagetitle','item', 'meta', 'subscriber', 'currencyData', 'site_url'));
                } else {
                    Notifier::error('Failed to subscribe');
                    return view('@Axisubs/Site/subscribe/subscribe.twig', compact('pagetitle','item', 'meta', 'currencyData', 'site_url'));
                }
            } else if($http->get('task')=='update'){
                $result = Plans::updateSubscribe($http->all(), $item);
                if($result) {
                    Notifier::success('Subscribed successfully');
                    //wp_redirect($site_url.'index.php?axisubs_subscribes=subscribe');
                    //$subscriber = Plans::loadAllSubscribes();
                    $subscribtions_url = get_site_url().'/index.php?axisubs_subscribes=subscribe';
                    return view('@Axisubs/Site/subscribed/success.twig', compact('pagetitle','subscribtions_url'));
                } else {
                    Notifier::error('Failed to subscribe');
                    return view('@Axisubs/Site/subscribed/list.twig', compact('pagetitle','item', 'meta', 'subscriber', 'currencyData', 'site_url'));
                }
            }
            $subscriber = Plans::loadOldSubscriber($item);
            return view('@Axisubs/Site/subscribe/details.twig', compact('pagetitle','item', 'meta', 'subscriber', 'currencyData', 'site_url'));
        }
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