<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Models\Admin\Customers;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

use Axisubs\Helper\Status;
use Axisubs\Models\Plans;
use Axisubs\Helper\Currency;
use Axisubs\Helper\Pagination;

class CustomersController
{
    public function index(Http $http)
    {
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $role_names = wp_roles()->role_names;
        $pagetitle = 'Customers';
        $site_url = get_site_url();
        if ($http->get('task') == 'view' && $http->has('id')) {
            $customer = Customers::loadCustomer($http->get('id'));
            $planDetails = array();
//            if(isset($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id'])){
//                $planDetails = Customers::loadPlan($subscriber->meta[$subscriber->ID.'_axisubs_subscribe_plan_id']);
//                $status = new Status();
//                $statusCode = $subscriber->meta[$subscriber->ID.'_axisubs_subscribe_status'];
//                $statusText = $status->getStatusText($statusCode);
//            }
            return view('@Axisubs/Admin/customers/detail.twig', compact('pagetitle', 'subscriber', 'currencyData', 'site_url', 'planDetails', 'statusText'));
        } else if($http->get('task') == 'edit' && $http->has('id')){

        } else if($http->get('task') == 'delete' && $http->has('id')){

        }
        
        Customers::populateStates($http->all());
        // Load Listing layout
        $items = Customers::all();
        $pagination = new Pagination(Customers::$_start, Customers::$_limit, Customers::$_total);
        $paginationD['limitbox'] = $pagination->getLimitBox();
        $paginationD['links'] = $pagination->getPaginationLinks();
        return view('@Axisubs/Admin/customers/list.twig', compact('pagetitle', 'items', 'paginationD', 'site_url'));
    }
}