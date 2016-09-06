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
     * Get payment radio buttons
     * */
    public function loadPaymentOptions(){
        $apps = $this->getActivePaymentApps();
        $html = '';
        if(count($apps)){
            foreach ($apps as $key => $value){
                $html .= Event::trigger( $value['pluginFolder'].'_paymentOptionRadio', '', 'filter');
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