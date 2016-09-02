<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Helper;
use Axisubs\Models\Admin\Customers;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

use Axisubs\Helper\Status;
use Axisubs\Helper\Currency;
use Axisubs\Helper\Pagination;

class Customer
{
    public function index(Http $http)
    {
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $role_names = wp_roles()->role_names;
        $pagetitle = 'Customers';
        $site_url = get_site_url();
        if ($http->get('task') == 'view' && $http->get('id')) {
            $item = Customers::loadCustomer($http->get('id'));
            return view('@Axisubs/Admin/customers/detail.twig', compact('pagetitle', 'item', 'currencyData', 'site_url'));
        } else if($http->get('task') == 'edit' && $http->get('id')){
            $pagetitle = 'Edit Customer';
            if($http->get('edit_task') == 'save'){
                $result = Customers::saveCustomer($http->all(), $http->get('id'));
                if($result){
                    Notifier::success('Customer details updated successfully');
                } else {
                    Notifier::error('Failed to update');
                }
            }
            $item = Customers::loadCustomer($http->get('id'));
            $wp_userDetails = Helper::getUserDetails($http->get('id'));
            if($wp_userDetails->data->user_login){
                $wp_userD['user_login'] = $wp_userDetails->data->user_login;
            } else {
                $wp_userD = array();
            }
            return view('@Axisubs/Admin/customers/edit.twig', compact('pagetitle', 'item', 'currencyData', 'site_url', 'wp_userD'));
        } else if($http->get('task') == 'delete' && $http->get('id')){
            $result = Customers::deleteCustomer($http->get('id'));
            if($result){
                Notifier::success('Customer deleted successfully');
            } else {
                Notifier::error('Failed to delete');
            }
        } else if($http->get('task') == 'new'){
            $newuser = 1;
            $newCustomersSelectBox = Customers::loadNewUsersNotInCustomersSelectbox();
            $pagetitle = 'Add Customer';
            return view('@Axisubs/Admin/customers/edit.twig', compact('pagetitle', 'item', 'currencyData', 'site_url', 'wp_userD', 'newCustomersSelectBox', 'newuser'));
        }
        
        Customers::populateStates($http->all());
        // Load Listing layout
        $items = Customers::all();
        $pagination = new Pagination(Customers::$_start, Customers::$_limit, Customers::$_total);
        $paginationD['limitbox'] = $pagination->getLimitBox();
        $paginationD['links'] = $pagination->getPaginationLinks();
        return view('@Axisubs/Admin/customers/list.twig', compact('pagetitle', 'items', 'paginationD', 'site_url'));
    }

    public function loadCustomerSubscriptions(){
        $http = Http::capture();
        $id = $http->get('id');
        $items = Customers::loadSubscriptionsByUserId($id);
        return view('@Axisubs/Admin/customers/moresubscriptions.twig', compact('items'));
    }

    public function loadCustomerDetails(){
        $http = Http::capture();
        $id = $http->get('id');
        $result = Customers::loadCustomerDetailsByUserId($id);
        echo json_encode($result);
    }

    public function addCustomer(){
        $http = Http::capture();
        $result = Customers::addNewCustomer($http->all());
        echo json_encode($result);
    }
}