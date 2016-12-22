<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

use Axisubs\Models\Admin\App;
use Events\Event;
use Herbert\Framework\Http;

class PaymentPlugins
{
    /**
     * Get active payment apps
     * */
    public function getActivePaymentApps(){
        $apps = App::getActivePaymentApps();
        
        return $apps;
    }

    /**
     * Get All payment apps
     * */
    public function getAllPaymentApps(){
        $apps = App::getActivePaymentApps(0);

        return $apps;
    }

    /**
     * Get payment radio buttons
     * */
    public function loadPaymentOptions($item){
        $apps = $this->getActivePaymentApps();
        $html = '';
        $planPrefix = $item->ID.'_'.$item->post_type.'_';
        $selectedPayment = isset($item->meta[$planPrefix.'payment_plugins']) ? $item->meta[$planPrefix.'payment_plugins'] : '';
        if(count($apps)){
            foreach ($apps as $key => $value){
                if($selectedPayment == ''){
                    $html .= Event::trigger( $value['pluginFolder'].'_paymentOptionRadio', '', 'filter');
                } else if(in_array($value['pluginFolder'], explode(",", $selectedPayment))){
                    $html .= Event::trigger( $value['pluginFolder'].'_paymentOptionRadio', '', 'filter');
                }

            }
        }
        return $html;
    }

    /**
     * Load payment form
     * */
    public function loadPaymentForm($paymentType, $subscription, $plan){
        $html = Event::trigger( $paymentType.'_paymentForm', array($subscription, $plan), 'filter');
        return $html;
    }

    public function executePaymentTasks(){
        $http = Http::capture();
        $html = Event::trigger( $http->get('payment_type').'_paymentTask', '', 'filter');

        return $html;
    }
}