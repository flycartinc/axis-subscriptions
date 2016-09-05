<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Site;

use Axisubs\Models\Site\Plans;
use Axisubs\Helper\Currency;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper\Status;
use Axisubs\Helper\FrontEndMessages;
use Axisubs\Helper;
use Axisubs\Controllers\Controller;

class Subscribe extends Controller{

    public $_controller = 'Subscribe';
    public $_path = 'Site';
    public $message = null;

    /**
     * Show My Subscriptions Default layout
     * */
    public function index(){
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        $pagetitle = "Subscriptions";
        $subscribtions_url = get_site_url().'/index.php?axisubs_subscribes=subscribe';
        $wp_user = Helper::getUserDetails();
        $user_id = $wp_user->ID;
        if($user_id){
            $subscribers = Plans::loadAllSubscribes();
        } else {
            $subscribers = array();
        }
        $message = $this->message;
        return view('@Axisubs/Site/subscribed/list.twig', compact('pagetitle', 'subscribtions_url', 'subscribers', 'currencyData', 'site_url', 'message'));
    }

    /**
     * Subscription detail view
     * */
    public function view(){
        $http = Http::capture();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        $pagetitle = "Subscriptions";
        $subscribtions_url = get_site_url().'/index.php?axisubs_subscribes=subscribe';
        $wp_user = Helper::getUserDetails();
        $user_id = $wp_user->ID;
        if($http->get('sid')) {
            $subscriber = Plans::loadSubscriber($http->get('sid'));
            if(isset($subscriber->ID) && isset($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id'])){
                if($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_user_id'] == $user_id) {
                    $planDetails = Plans::loadPlan($subscriber->meta[$subscriber->ID . '_axisubs_subscribe_plan_id']);
                    $status = new Status();
                    $statusCode = $subscriber->meta[$subscriber->ID . '_axisubs_subscribe_status'];
                    $statusText = $status->getStatusText($statusCode);
                    return view('@Axisubs/Site/subscribed/singlesubscription.twig', compact('pagetitle', 'subscribtions_url', 'subscriber', 'currencyData', 'site_url', 'planDetails', 'statusText'));
                } else {
                    $this->message = FrontEndMessages::failure('Invalid access');
                }
            } else {
                $this->message = FrontEndMessages::failure('Invalid access');
            }
        }
        return $this->index();
    }
}