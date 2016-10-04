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
use Axisubs\Helper\Countries;

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
        $subscribtions_url = $this->getAxiSubsURLs('subscribe');
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
        $http = $this->getQueryStringData($http);
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        if($http->get('slug') == '' || $http->get('id') == '') {
            return $this->index();
        } else {
            $pagetitle = "Order Summary";
            $item = Plans::loadPlan($http->get('id'));
            if($item) {
                $meta = $item->meta;

                //Check eligibility
                $eligible = Plans::isEligible($item);
                if ($eligible) {
                    $data['plan_url'] = $this->getAxiSubsURLs('plan', 'view', $http->get('id'), $http->get('slug'));
                    $subscriber = Plans::loadOldSubscriber($item);
                    $user = Plans::getUserDetails();
                    //From WP
                    $wp_user = ManageUser::getInstance()->getUserDetails();
                    $user_id = $wp_user->ID;
                    if ($meta[$item->ID . '_axisubs_plans_type'] != 'free') {
                        //For loading payment options
                        $payment = new PaymentPlugins();
                        $data['paymentMethods'] = $payment->loadPaymentOptions();
                    }
                    $custProvince = $custCountry = '';
                    if (!empty($user)) {
                        $custPrefix = $user->ID . '_' . $user->post_type . '_';
                        $custProvince = $user->meta[$custPrefix . 'province'];
                        $custCountry = $user->meta[$custPrefix . 'country'];
                    }
                    $modelZone = $this->getModel('Zones', 'Admin');
                    $data['country'] = Countries::getCountriesSelectBox($custCountry, 'axisubs[subscribe][country]', 'axisubs_subscribe_country', 'required');
                    $data['province'] = $modelZone->getProvinceSelectBox($custCountry, $custProvince, 'axisubs[subscribe][province]', 'axisubs_subscribe_province', 'required');
                    return view('@Axisubs/Site/subscribe/details.twig', compact('pagetitle', 'item', 'meta', 'subscriber', 'currencyData', 'site_url', 'user', 'user_id', 'data'));
                } else {
                    $this->message = FrontEndMessages::failure('You have already subscribed for this plan. Please try another plan / try again after end date of current subscription.');
                    return $this->index();
                }
            } else {
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
        $http = $this->getQueryStringData($http);
        if(!$http->get('id')) {
            $resultArray['message'] = 'Invalid Plan';
            $resultArray['status'] = 'Failed';
        } else {
            $item = Plans::loadPlan($http->get('id'));
            if($item) {
                $meta = $item->meta;
                //Check eligibility
                $eligible = Plans::isEligible($item);
                if ($eligible) {
                    $plan_confirm_url = $this->getAxiSubsURLs('plan', 'confirm', $http->get('id'), $meta[$item->ID . '_axisubs_plans_slug']);
                    $result = $model->addSubscribe($http->all(), $item);
                    if ($result) {
                        $data['hasActiveSubs'] = count($model->existAlready);
                        $subscriber = Plans::loadSubscriber($result);
                        if ($meta[$item->ID . '_axisubs_plans_type'] != 'free') {
                            if ($http->get('payment', '') != '') {
                                //For loading payment options
                                $payment = new PaymentPlugins();
                                $data['paymentForm'] = $payment->loadPaymentForm($http->get('payment'), $subscriber, $item);
                                $resultArray['message'] = 'Loading please wait..';
                                $resultArray['status'] = 'success';
                                $resultArray['redirect'] = $plan_confirm_url;
                            } else {
                                $resultArray['message'] = 'Invalid payment option';
                                $resultArray['status'] = 'Failed';
                            }
                        } else {
                            $resultArray['message'] = 'Loading please wait..';
                            $resultArray['status'] = 'success';
                            $resultArray['redirect'] = $plan_confirm_url;
                        }
                    } else {
                        $resultArray['message'] = 'Failed to subscribe';
                        $resultArray['status'] = 'Failed';
                    }
                } else {
                    $resultArray['message'] = 'You have already subscribed for this plan. Please try another plan / try again after end date of current subscription.';
                    $resultArray['status'] = 'Failed';
                }
            } else {
                $resultArray['message'] = 'Invalid Plan';
                $resultArray['status'] = 'Failed';
            }
        }
        echo json_encode($resultArray);
    }

    /**
     * confirm a subscription
     * */
    public function confirm(){
        $model = $this->getModel('Plans');
        $data = array();
        $http = Http::capture();
        $http = $this->getQueryStringData($http);
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        if($http->get('slug')=='') {
            return $this->index();
        } else {
            $pagetitle = "Order Summary";
            $item = Plans::loadPlan($http->get('id'));
            if($item) {
                $meta = $item->meta;
                //Check eligibility
                $eligible = Plans::isEligible($item);
                if ($eligible) {
                    $sessionData = Session()->get('axisubs_subscribers');
                    if (isset($sessionData[$http->get('id')]) && $sessionData[$http->get('id')]->subscriberId) {
                        $result = $sessionData[$http->get('id')]->subscriberId;
                    } else {
                        $result = 0;
                    }
                    $data['plan_url'] = $this->getAxiSubsURLs('plan', 'view', $http->get('id'), $http->get('slug'));
                    if ($result) {
                        $data['hasActiveSubs'] = count($model->existAlready);
                        $subscriber = Plans::loadSubscriber($result);
                        if ($meta[$item->ID . '_axisubs_plans_type'] != 'free') {
                            $paymentType = $subscriber->ID . '_'.$subscriber->post_type.'_payment_type';
                            if (isset($subscriber->meta[$paymentType]) &&
                                $subscriber->meta[$paymentType] != '') {
                                //For loading payment options
                                $payment = new PaymentPlugins();
                                $data['paymentForm'] = $payment->loadPaymentForm($subscriber->meta[$paymentType], $subscriber, $item);
                            } else {
                                $this->message = FrontEndMessages::failure('Invalid payment option');
                                return $this->index();
                            }
                        } else {
                            $data['plan_url'] = $this->getAxiSubsURLs('plan', 'updateFreePlan', $http->get('id'), $http->get('slug'));
                        }
                        $custCountry = $subscriber->meta[$subscriber->ID . '_axisubs_subscribe_country'];
                        $custProvince = $subscriber->meta[$subscriber->ID . '_axisubs_subscribe_province'];
                        $modelZone = $this->getModel('Zones', 'Admin');
                        $data['province'] = $modelZone->getProvinceName($custProvince, $custCountry);
                        $data['country'] = Countries::getCountryName($custCountry);

                        return view('@Axisubs/Site/subscribe/subscribe.twig', compact('pagetitle', 'item', 'meta', 'subscriber', 'currencyData', 'site_url', 'data'));
                    } else {
                        $message = FrontEndMessages::failure('Invalid request');
                        return view('@Axisubs/Site/subscribe/subscribe.twig', compact('pagetitle', 'item', 'meta', 'currencyData', 'site_url', 'message'));
                    }
                } else {
                    $this->message = FrontEndMessages::failure('You have already subscribed for this plan. Please try another plan / try again after end date of current subscription.');
                    return $this->index();
                }
            } else {
                return $this->index();
            }
        }
    }

    /**
     * update free plan
     * */
    public function updateFreePlan(){
        $http = Http::capture();
        $http = $this->getQueryStringData($http);
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
                    //$subscribtions_url = get_site_url() . '/index.php?axisubs_subscribes=subscribe';
                    $subscribtions_url = $this->getAxiSubsURLs('subscribe');
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
        $http = $this->getQueryStringData($http);
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
        //$subscribtions_url = get_site_url() . '/index.php?axisubs_subscribes=subscribe';
        $subscribtions_url = $this->getAxiSubsURLs('subscribe');
        return view('@Axisubs/Site/subscribed/success.twig', compact('pagetitle', 'subscribtions_url', 'message'));
    }
}