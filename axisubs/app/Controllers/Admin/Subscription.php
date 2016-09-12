<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Models\Admin\Subscriptions;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper\AxisubsRedirect;
use Axisubs\Helper\Status;
use Axisubs\Helper\Currency;
use Axisubs\Helper\Pagination;
use Axisubs\Controllers\Controller;
use Axisubs\Models\Admin\Customers;

class Subscription extends Controller
{
    public $_controller = 'Subscription';

    /**
     * Default page
     * */
    public function index()
    {
        $http = Http::capture();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $pagetitle = 'Subscriptions';
        $site_url = get_site_url();
        if ($http->has('sid')) {
            $subscriber = Subscriptions::loadSubscriber($http->get('sid'));
            $planDetails = array();
            if(isset($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id'])){
                $planDetails = Subscriptions::loadPlan($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id']);
                $status = new Status();
                $statusCode = $subscriber->meta[$subscriber->ID.'_axisubs_subscribe_status'];
                $statusText = $status->getStatusText($statusCode);
            }
            return view('@Axisubs/Admin/subscriptions/detail.twig', compact('pagetitle', 'subscriber', 'currencyData', 'site_url', 'planDetails', 'statusText'));
        }
        Subscriptions::populateStates($http->all());
        // Load Listing layout
        $items = Subscriptions::all();
        $pagination = new Pagination(Subscriptions::$_start, Subscriptions::$_limit, Subscriptions::$_total);
        $paginationD['limitbox'] = $pagination->getLimitBox();
        $paginationD['links'] = $pagination->getPaginationLinks();
        return view('@Axisubs/Admin/subscriptions/list.twig', compact('pagetitle', 'items', 'paginationD'));
    }

    /**
     * New / Edit Subscription
     * */
    public function edit(){
        $http = Http::capture();
        $site_url = get_site_url();
        if ($http->get('user_id')) {
            $data['customer'] = Customers::loadCustomer($http->get('user_id'));
            $planid = '';
            if ($http->get('sid')) {
                $data['subscription'] = Subscriptions::loadSubscriber($http->get('sid'));
                $subscriptionPrefix = $data['subscription']->ID.'_'.$data['subscription']->post_type.'_';
                $planid = $data['subscription']->meta[$subscriptionPrefix.'plan_id'];
            }
            $data['planSelectbox'] = Subscriptions::loadPlanSelectbox($planid);
            return view('@Axisubs/Admin/subscription/edit.twig', compact('pagetitle', 'data', 'role_names', 'site_url'));
        } else {
            AxisubsRedirect::redirect('?page=subscriptions-index');
        }
    }

    /**
     * delete
     * */
    public function delete()
    {
        $http = Http::capture();
        if($http->get('id')) {
            $result = Subscriptions::deleteSubscriptions($http->get('id'));
            if($result){
                Notifier::success('Subscription deleted successfully');
            } else {
                Notifier::error('Failed to delete');
            }
        }

        return $this->index();
    }

    /**
     * Load plan details for auto populate
     * */
    public function loadPlanDetails(){
        $http = Http::capture();
        $id = $http->get('id');
        $data = Subscriptions::loadPlan($id);
        $result = array();
        if(!empty($data)){
            $prefix = $data->ID.'_'.$data->post_type.'_';
            $meta = $data->meta;
            $result['plan_id'] = $data->ID;
            foreach ($meta as $key => $value){
                $field = explode($prefix, $key);
                if(isset($field['1'])) {
                    $result[$field['1']] = $value;
                } else {
                    $result[$field['0']] = $value;
                }
            }
            $response['status'] = 'success';
            $response['fields'] = $result;
        } else {
            $response['status'] = 'failed';
            $response['fields'] = $result;
        }
        echo json_encode($response);
    }
    
    /**
     * Add new subscription
     * */
    public function addNewSubscription(){
        $http = Http::capture();
        $user_id = $http->get('user_id');
        $plan_id = $http->get('axisubs_plan');
        $start_on = $http->get('subscribe_start_on', '');
        $model = $this->getModel('Subscriptions');
        $sub_id = $http->get('sid', 0);
        $result = $model->addSubscription($user_id, $plan_id, $sub_id, $start_on);
        if($result){
            Notifier::success('Subscription created successfully');
        } else {
            Notifier::error('Failed to create subscription');
        }
        return $this->index();
    }
}