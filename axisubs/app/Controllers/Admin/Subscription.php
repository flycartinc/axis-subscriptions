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
        if ($http->get('user_id')) {
            $data['customer'] = Customers::loadCustomer($http->get('user_id'));
            $data['planSelectbox'] = Subscriptions::loadPlanSelectbox();
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
}