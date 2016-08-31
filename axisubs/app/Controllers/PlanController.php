<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers;

use Axisubs\Models\Plans;
use Axisubs\Models\User;
use Axisubs\Helper\Currency;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Herbert\Framework\Response;
use Axisubs\Helper;
use Axisubs\Helper\Duration;
use Axisubs\Helper\FrontEndMessages;

class PlanController{
    //Show all Plans
    public function showAllPlans(Http $http){

        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        $subscribtions_url = get_site_url().'/index.php?axisubs_subscribes=subscribe';
        if($http->get('slug')=='') {
        } else {
            $pagetitle = "Order Summary";
            $item = Plans::loadPlan($http->get('id'));
            $meta = $item->meta;

            //Check eligibility
            $eligible = Plans::isEligible($item);
            if($eligible) {
                //for adding subscriber
                if ($http->get('task') == 'save') {
                    $result = Plans::addSubscribe($http->all(), $item);
                    if ($result) {
                        $subscriber = Plans::loadSubscriber($result);
                        return view('@Axisubs/Site/subscribe/subscribe.twig', compact('pagetitle', 'item', 'meta', 'subscriber', 'currencyData', 'site_url'));
                    } else {
                        $message = FrontEndMessages::failure('Failed to subscribe');
                        return view('@Axisubs/Site/subscribe/subscribe.twig', compact('pagetitle', 'item', 'meta', 'currencyData', 'site_url', 'message'));
                    }
                } else if ($http->get('task') == 'update') {
                    $result = Plans::updateSubscribe($http->all(), $item);
                    if ($result) {
                        $message = FrontEndMessages::success('Subscribed successfully');
                        //wp_redirect($site_url.'index.php?axisubs_subscribes=subscribe');
                        //$subscriber = Plans::loadAllSubscribes();
                        $subscribtions_url = get_site_url() . '/index.php?axisubs_subscribes=subscribe';
                        return view('@Axisubs/Site/subscribed/success.twig', compact('pagetitle', 'subscribtions_url', 'message'));
                    } else {
                        $message = FrontEndMessages::failure('Failed to subscribe');
                        return view('@Axisubs/Site/subscribed/list.twig', compact('pagetitle', 'item', 'meta', 'subscriber', 'currencyData', 'site_url', 'subscribtions_url', 'message'));
                    }
                }
                $subscriber = Plans::loadOldSubscriber($item);
                $user = Plans::getUserDetails();
                $wp_user = Helper::getUserDetails();
                $user_id = $wp_user->ID;
                return view('@Axisubs/Site/subscribe/details.twig', compact('pagetitle', 'item', 'meta', 'subscriber', 'currencyData', 'site_url', 'user', 'user_id'));
            } else {
                $message = FrontEndMessages::failure('You have already subscribed for this plan. Please try another plan / try again after end date of current subscription.');
            }
        }
        $duration = new Duration();
        $unitInWords = $duration->getDurationInFormatInArray();
        $pagetitle = "Plans";
        $items = Plans::allFrontEndPlans();
        return view('@Axisubs/Site/plans/list.twig', compact('pagetitle','items', 'currencyData', 'site_url', 'subscribtions_url', 'unitInWords', 'message'));
    }

    public function showSelectedPlan(){
        return view('@Axisubs/Site/subscribe/details.twig');
    }
}