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
use Events\Event;

class Plan extends Controller{

    public $_controller = 'Plan';
    public $_path = 'Site';
    public $message = null;

    /**
     * Show All Plans Default layout
     * */
    public function index($id = ''){
        $http = Http::capture();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $site_url = get_site_url();
        $subscribtions_url = $this->getAxiSubsURLs('subscribe');
        $duration = new Duration();
        $unitInWords = $duration->getDurationInFormatInArray();
        $pagetitle = "Plans";
        $items = Plans::allFrontEndPlans($id);
        $message = $this->message;
        return view('@Axisubs/Site/plans/list.twig', compact('pagetitle','items', 'currencyData', 'site_url', 'subscribtions_url', 'unitInWords', 'message'));
    }

    /**
     * Show Selected Plans Default layout
     * */
    public function selectedPlans($id){
        if($id == '' || empty($id)){
            return $this->index();
        } else {
            return $this->index($id);
        }
    }

    /**
     * Show Subscribe button / Shortcode [AxisubsSubscribeButton id=5]
     * */
    public function showSubscribeButton($id){
        if($id == '' || empty($id)){
            return '';
        } else {
            $plan = Plans::loadPlan($id);
            if($plan){
                $plan_url = $this->getAxiSubsURLs('plan', 'view', $plan->ID, $plan->meta[$plan->ID.'_'.$plan->post_type.'_slug']);
            } else {
                $plan_url = '';
            }
            return view('@Axisubs/Site/plans/subscribe_button.twig', compact('plan_url'));
        }
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
                //Check eligibility
                $eligible = Plans::isEligible($item);
                if ($eligible) {
                    if($http->get('upgrade'))
                        $data['upgrade'] = $http->get('upgrade');
                    $data['plan_url'] = $this->getAxiSubsURLs('plan', 'view', $http->get('id'), $http->get('slug'));
                    $data['content_after_price'] = '';
                    $subscriber = Plans::loadOldSubscriber($item);
                    $user = Plans::getUserDetails();
                    //From WP
                    $wp_user = ManageUser::getInstance()->getUserDetails();
                    $user_id = $wp_user->ID;

                    //pre process the plan
                    $model = $this->getModel('Plans');
                    $model->preProcessPlan($item, $data, $subscriber);

                    $meta = $item->meta;
                    if ($meta[$item->ID . '_axisubs_plans_type'] != 'free') {
                        //For loading payment options
                        $payment = new PaymentPlugins();
                        $data['paymentMethods'] = $payment->loadPaymentOptions($item);
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
                    $data['plugin_url'] = AXISUBS_PLUGIN_URL;

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
        $error = array('status' => 0, 'fields' => '');
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
                    //Validate Fields
                    Event::trigger( 'axisubs-app-validateSubscribeForm', array(&$error, $http->all()));
                    if($error['status']){
                        $resultArray['message'] = implode('<br>', $error['fields']);
                        $resultArray['status'] = 'Failed';
                        $resultArray['invalidfields'] = $error['fields'];
                    } else {
                        $additionalURL = '';
                        if($http->get('upgrade')){
                            $additionalURL = 'upgrade='.$http->get('upgrade');
                        }
                        $plan_confirm_url = $this->getAxiSubsURLs('plan', 'confirm', $http->get('id'), $meta[$item->ID . '_axisubs_plans_slug'], $additionalURL);
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
                    $data['content_after_price'] = '';
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
                        //pre process the plan
                        $model->preProcessPlan($item, $data, $subscriber, 'confirm');

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
                        $data['plugin_url'] = AXISUBS_PLUGIN_URL;
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
                    Event::trigger('axisubs-app_clearSession', '');
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

    /**
     *
     * */
    public function app(){
        $http = Http::capture();
        if($http->get('app', '') != ''){
            echo Event::trigger($http->get('app', '').'_ajax', array($http), 'filter');
        }
    }
}