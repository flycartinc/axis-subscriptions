<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

use Axisubs\Models\Admin\Customers;

class Common
{
    /**
     * For generating invoice number
     * */
    public function getInvoiceNumber($subscription_id){

        return 'axisubs_wp-'.$subscription_id; //TODO: Make it dynamic.
    }

    /**
     * get subscription id from invoice number
     * */
    public function getSubscriptionIdFromInvoiceNumber($invoice){

        return str_replace('axisubs_wp-', '', $invoice); //TODO: Make it dynamic.
    }

    /**
     * For Round the value
     * */
    public static function roundPrice($price){
        return round($price, 2);
    }

    /**
     * For setting the customer details in session for calculating tax
     * */
    public static function setCustomerDetailsInSession(){
        $user_details = ManageUser::getInstance()->getUserDetails();
        if(isset($user_details->ID) && $user_details->ID){
            $customer = Customers::getCustomerDetails($user_details->ID);
            $customerMeta = $customer->meta;
            $customerPrefix = $customer->ID.'_'.$customer->post_type.'_';
            $session = Session();
            if(isset($customerMeta[$customerPrefix.'country']) && $customerMeta[$customerPrefix.'country'] != ''){
                $session->set('customer_billing_country', $customerMeta[$customerPrefix.'country']);
            }
            if(isset($customerMeta[$customerPrefix.'province']) && $customerMeta[$customerPrefix.'province'] != ''){
                $session->set('customer_billing_state', $customerMeta[$customerPrefix.'province']);
            }
            if(isset($customerMeta[$customerPrefix.'pincode']) && $customerMeta[$customerPrefix.'pincode'] != ''){
                $session->set('customer_billing_zip', $customerMeta[$customerPrefix.'pincode']);
            }
            if(isset($customerMeta[$customerPrefix.'city']) && $customerMeta[$customerPrefix.'city'] != ''){
                $session->set('customer_billing_city', $customerMeta[$customerPrefix.'city']);
            }
            if(isset($customerMeta[$customerPrefix.'country']) && $customerMeta[$customerPrefix.'country'] != ''){
                $session->set('customer_billing_country', $customerMeta[$customerPrefix.'country']);
            }
            if(isset($customerMeta[$customerPrefix.'vat_number']) && $customerMeta[$customerPrefix.'vat_number'] != ''){
                $session->set('customer_billing_vat_number', $customerMeta[$customerPrefix.'vat_number']);
            }
        }
    }
}