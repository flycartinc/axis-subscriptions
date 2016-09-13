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
use Herbert\Framework\Response;
use Axisubs\Helper;
use Axisubs\Helper\Duration;
use Axisubs\Helper\FrontEndMessages;
use Axisubs\Controllers\Controller;
use Axisubs\Helper\PaymentPlugins;
use Axisubs\Helper\ManageUser;

class Plan extends Controller{

    public $_controller = 'Plan';
    public $_path = 'Site';
    public $message = null;

    /**
     * Show All Plans Default layout
     * */
    public function index(){
        $http = Http::capture();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        $subscribtions_url = get_site_url().'/index.php?axisubs_subscribes=subscribe';
        $duration = new Duration();
        $unitInWords = $duration->getDurationInFormatInArray();
        $pagetitle = "Plans";
        $items = Plans::allFrontEndPlans();
        $message = $this->message;
        return view('@Axisubs/Site/plans/list.twig', compact('pagetitle','items', 'currencyData', 'site_url', 'subscribtions_url', 'unitInWords', 'message'));
    }

    /**
     * View a plan
     * */
    public function view(){
        $data = array();
        $http = Http::capture();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        if($http->get('slug')=='') {
            return $this->index();
        } else {
            $pagetitle = "Order Summary";
            $item = Plans::loadPlan($http->get('id'));
            $meta = $item->meta;

            //Check eligibility
            $eligible = Plans::isEligible($item);
            if($eligible) {
                $subscriber = Plans::loadOldSubscriber($item);
                $user = Plans::getUserDetails();
                //From WP
                $wp_user = ManageUser::getInstance()->getUserDetails();
                $user_id = $wp_user->ID;
                if($meta[$item->ID.'_axisubs_plans_type'] != 'free'){
                    //For loading payment options
                    $payment = new PaymentPlugins();
                    $data['paymentMethods'] = $payment->loadPaymentOptions();
                }

                return view('@Axisubs/Site/subscribe/details.twig', compact('pagetitle', 'item', 'meta', 'subscriber', 'currencyData', 'site_url', 'user', 'user_id', 'data'));
            } else {
                $this->message = FrontEndMessages::failure('You have already subscribed for this plan. Please try another plan / try again after end date of current subscription.');
                return $this->index();
            }
        }
    }

    /**
     * save / create a subscription
     * */
    public function save(){
        $model = $this->getModel('Plans');
        $data = array();
        $http = Http::capture();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        if($http->get('slug')=='') {
            return $this->index();
        } else {
            $pagetitle = "Order Summary";
            $item = Plans::loadPlan($http->get('id'));
            $meta = $item->meta;

            //Check eligibility
            $eligible = Plans::isEligible($item);
            if($eligible) {
                $result = $model->addSubscribe($http->all(), $item);
                if ($result) {
                    $data['hasActiveSubs'] = count($model->existAlready);
                    $subscriber = Plans::loadSubscriber($result);
                    if($meta[$item->ID.'_axisubs_plans_type'] != 'free'){
                        if($http->get('payment', '') != ''){
                            //For loading payment options
                            $payment = new PaymentPlugins();
                            $data['paymentForm'] = $payment->loadPaymentForm($http->get('payment'), $subscriber, $item);
                        } else {
                            $this->message = FrontEndMessages::failure('Invalid payment option');
                            return $this->index();
                        }
                    }
                    return view('@Axisubs/Site/subscribe/subscribe.twig', compact('pagetitle', 'item', 'meta', 'subscriber', 'currencyData', 'site_url', 'data'));
                } else {
                    $message = FrontEndMessages::failure('Failed to subscribe');
                    return view('@Axisubs/Site/subscribe/subscribe.twig', compact('pagetitle', 'item', 'meta', 'currencyData', 'site_url', 'message'));
                }
            } else {
                $this->message = FrontEndMessages::failure('You have already subscribed for this plan. Please try another plan / try again after end date of current subscription.');
                return $this->index();
            }
        }
    }

    /**
     * update free plan
     * */
    public function updateFreePlan(){
        $http = Http::capture();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        if($http->get('slug')=='') {
            return $this->index();
        } else {
            $pagetitle = "Order Summary";
            $item = Plans::loadPlan($http->get('id'));
            $meta = $item->meta;

            //Check eligibility
            $eligible = Plans::isEligible($item);
            if($eligible) {
                $result = Plans::updateFreeSubscribe($http->all(), $item);
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
            } else {
                $this->message = FrontEndMessages::failure('You have already subscribed for this plan. Please try another plan / try again after end date of current subscription.');
                return $this->index();
            }
        }
    }

    /**
     * Complete payment
     * */
    public function paymentComplete(){
        $http = Http::capture();
        if($http->get('payment_type') != ''){
            $sessionData = Session()->get('axisubs_subscribers');
            //if(isset($sessionData['current_subscription_id']) && $sessionData['current_subscription_id']){
            if((isset($sessionData['current_subscription_id']) && $sessionData['current_subscription_id']) || ($http->get('apptask') == 'notify')){
                $payment = new PaymentPlugins();
                $result = $payment->executePaymentTasks();
                //Session()->set('axisubs_subscribers', null);
                if($http->get('apptask') != 'notify') {
                    Session()->set('axisubs_subscribers', null);

                    if($result['status'] == 200){
                        $message = FrontEndMessages::success($result['message']);
                    } else {
                        $message = FrontEndMessages::failure($result['message']);
                    }
                }
            } else {
                $message = FrontEndMessages::failure('Session expired');
            }
        } else {
            $message = FrontEndMessages::failure('Invalid Request');
        }
        $subscribtions_url = get_site_url() . '/index.php?axisubs_subscribes=subscribe';
        return view('@Axisubs/Site/subscribed/success.twig', compact('pagetitle', 'subscribtions_url', 'message'));
    }
}