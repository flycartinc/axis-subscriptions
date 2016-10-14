<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace AxisubsAppPaypal\Controllers\Admin;

use AxisubsAppPaypal\Controllers\Controller;
use AxisubsAppPaypal\Models\Admin\AxisubsAppPaypal as AxisubsAppPaypalModel;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper\Common;
use Axisubs\Models\Admin\Config;

class AxisubsAppPaypal extends Controller
{
    public $_controller = 'AxisubsAppPaypal';
    public $_element = 'app_paypal';
    public $_folder = 'axisubs-app-payment-paypal';

    /**
     * Default App view
     * */
    public static function loadAppView(){
        $currentObject = new AxisubsAppPaypal();
        $currentObject->index();
    }

    /**
     * Default layout
     * */
    public function index(){
        $pagetitle = "Paypal Configuration";
        $model = $this->getModel();
        $item = $model->getConfig();
        $data = view('@AxisubsAppPaypal/Admin/Form/edit.twig', compact('pagetitle', 'item'));
        if($data->getStatusCode() == '200'){
            echo $data->getBody();
        } else {
            echo "Something goes wrong";
        }
    }

    public function getPostURL($sandbox){
        $url = $sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';
        $url = 'https://' . $url . '/cgi-bin/webscr';
        return $url;
    }

    public function loadPaymentForm($subscription, $plans){
        $settings = Config::getInstance();
        $subscriptionPrefix = $subscription->ID.'_axisubs_subscribe_';
        $plansPrefix = $plans->ID.'_axisubs_plans_';
        $model = $this->getModel();

        $vars = new \stdClass();
        $vars->currency_code = $settings->getConfigData('currency', 'USD');
        $vars->orderpayment_amount = $subscription->meta[$subscriptionPrefix.'total_price'];
        $vars->orderpayment_type = $this->_element;
        $vars->display_name = $model->getConfigData('title');
        $vars->onbeforepayment_text = $model->getConfigData('title');
        $vars->button_text = $model->getConfigData('button_text', 'Subscribe');'';
        $vars->image = $model->getConfigData('image');

        $vars->cbt = $model->getConfigData('cbt', 'Return to Site');
        $vars->cpp_header_image = $model->getConfigData('cpp_header_image', 'acs');
        $vars->cpp_headerback_color = $model->getConfigData('cpp_headerback_color', '#ffffff');
        $vars->cpp_headerborder_color = $model->getConfigData('cpp_headerborder_color', '#ffffff');

        // set Payment plugin variables
        if($model->getConfigData('sandbox', 1)){
            $vars->merchant_email = trim($model->getConfigData('sandbox_email'));
        }else{
            $vars->merchant_email = trim($model->getConfigData('merchant_email'));
        }

        $vars->cmd = '_xclick';
        $vars->charset = 'windows-1252';

        $vars->item_name = $plans->meta[$plansPrefix.'name'];
        $hastrial = $recurring = 0;
        if ( $plans->meta[$plansPrefix.'type'] == 'recurring_with_trial' ) {
            $vars->a1 = 0;
            $vars->p1 = $plans->meta[$plansPrefix.'trial_period'];
            $vars->t1 = $plans->meta[$plansPrefix.'trial_period_units'];
            $vars->has_trial = $hastrial = 1;

        }

        if ( $plans->meta[$plansPrefix.'type'] == 'recurring' || $plans->meta[$plansPrefix.'type'] == 'recurring_with_trial' ) {
            $vars->cmd =  '_xclick-subscriptions' ;
            // send the price without setup fee
            $vars->a3 = $plans->meta[$plansPrefix.'price'];
            // TODO: fix it to recurring_total at of now this includes setup fee as well
            $vars->p3 = $plans->meta[$plansPrefix.'period'];
            $vars->t3 = $plans->meta[$plansPrefix.'period_units']; //D,M,W
            $vars->billing_cycles = $plans->meta[$plansPrefix.'billing_cycles'];
            if($hastrial){
                $vars->recurring_total = $plans->meta[$plansPrefix.'billing_cycles']+1;
            } else {
                $vars->recurring_total = $plans->meta[$plansPrefix.'billing_cycles'];
            }
            $vars->recurring = $recurring = 1;
            $vars->sra = 1 ; // failiure reattempt flag
            $vars->charset = 'utf-8';
        }

        $commonObj = new Common();

        $vars->post_url = $this->getPostURL($model->getConfigData('sandbox'));
        $vars->return_url = get_site_url().'/?axisubs_plan=plans&task=paymentComplete&payment_type='.$this->_folder.'&apptask=display';
        $vars->cancel_url = get_site_url().'/?axisubs_plan=plans&task=paymentComplete&payment_type='.$this->_folder.'&apptask=cancel';
        $vars->notify_url = get_site_url().'/?axisubs_plan=plans&task=paymentComplete&payment_type='.$this->_folder.'&apptask=notify';

        if ( $plans->meta[$plansPrefix.'type'] == 'recurring' || $plans->meta[$plansPrefix.'type'] == 'recurring_with_trial' ) {
            $vars->first_name   = html_entity_decode($subscription->meta[$subscriptionPrefix.'first_name'], ENT_QUOTES, 'UTF-8');
            $vars->last_name    = html_entity_decode($subscription->meta[$subscriptionPrefix.'last_name'], ENT_QUOTES, 'UTF-8');
            $vars->email        = html_entity_decode($subscription->meta[$subscriptionPrefix.'email'], ENT_QUOTES, 'UTF-8');
            $vars->address_1    = html_entity_decode($subscription->meta[$subscriptionPrefix.'address_line1'], ENT_QUOTES, 'UTF-8');
            $vars->address_2    = html_entity_decode($subscription->meta[$subscriptionPrefix.'address_line2'], ENT_QUOTES, 'UTF-8');
            $vars->city         = html_entity_decode($subscription->meta[$subscriptionPrefix.'city'], ENT_QUOTES, 'UTF-8');
            $vars->country      = html_entity_decode($subscription->meta[$subscriptionPrefix.'country'], ENT_QUOTES, 'UTF-8');
            $vars->region       = html_entity_decode($subscription->meta[$subscriptionPrefix.'province'], ENT_QUOTES, 'UTF-8');
            $vars->postal_code  = html_entity_decode($subscription->meta[$subscriptionPrefix.'pincode'], ENT_QUOTES, 'UTF-8');
        } else {
            $vars->first_name   = $subscription->meta[$subscriptionPrefix.'first_name'];
            $vars->last_name    = $subscription->meta[$subscriptionPrefix.'last_name'];
            $vars->email        = $subscription->meta[$subscriptionPrefix.'email'];
            $vars->address_1    = $subscription->meta[$subscriptionPrefix.'address_line1'];
            $vars->address_2    = $subscription->meta[$subscriptionPrefix.'address_line2'];
            $vars->city         = $subscription->meta[$subscriptionPrefix.'city'];
            $vars->country      = $subscription->meta[$subscriptionPrefix.'country'];
            $vars->region       = $subscription->meta[$subscriptionPrefix.'province'];
            $vars->postal_code  = $subscription->meta[$subscriptionPrefix.'pincode'];
        }
        $vars->invoice      = $commonObj->getInvoiceNumber($subscription->ID);
        $vars->total        = $subscription->meta[$subscriptionPrefix.'total_price'];
        $vars->plan         = $plans;
        $vars->subscription = $subscription;

        $result = view('@AxisubsAppPaypal/Site/Form/form.twig', compact('vars'));
        if($result->getStatusCode() == '200'){
            return $result->getBody();
        } else {
            return "Something goes wrong";
        }
    }

    /**
     * Save paypal config
     * */
    public function save(){
        $http = Http::capture();
        $axisubPost = $http->get('axisubs');
        if (isset($axisubPost['payment'])) {
            $result = AxisubsAppPaypalModel::saveConfig($http->all());
            if ($result) {
                Notifier::success('Saved successfully');
            } else {
                Notifier::error('Failed to save');
            }
        }
        return $this->index();
    }

    public function display(){
        $model = $this->getModel();
        $result = $model->processPaymentReturn();

        return $result;
    }

    public function cancel(){
        $model = $this->getModel();
        $result = $model->processCancelPayment();

        return $result;
    }

    public function notify(){
        $this->display();
    }

    /**
     * For processing payment
     * */
    public function processPayment(){
        $http = Http::capture();
        //$model = $this->getModel();
        return $this->execute($http);
        //$model->processPaymentReturn();
    }
}