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
use Axisubs\Controllers\Controller;
use Axisubs\Helper\ManageUser;

class Customer extends Controller
{
    public $_controller = 'Customer';

    /**
     * Default page
     * */
    public function index()
    {
        $http = Http::capture();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $pagetitle = 'Customers';
        $site_url = get_site_url();
        Customers::populateStates($http->all());
        // Load Listing layout
        $items = Customers::all();
        $pagination = new Pagination(Customers::$_start, Customers::$_limit, Customers::$_total);
        $paginationD['limitbox'] = $pagination->getLimitBox();
        $paginationD['links'] = $pagination->getPaginationLinks();
        return view('@Axisubs/Admin/customers/list.twig', compact('pagetitle', 'items', 'paginationD', 'site_url'));
    }

    /**
     * View Page
     * */
    public function view()
    {
        $http = Http::capture();
        if($http->get('id')) {
            $currency = new Currency();
            $currencyData['code'] = $currency->getCurrencyCode();
            $currencyData['currency'] = $currency->getCurrency();
            $pagetitle = 'Customers';
            $site_url = get_site_url();

            $item = Customers::loadCustomer($http->get('id'));
            return view('@Axisubs/Admin/customers/detail.twig', compact('pagetitle', 'item', 'currencyData', 'site_url'));
        }

        return $this->index();
    }

    /**
     * Edit
     * */
    public function edit()
    {
        $http = Http::capture();
        if($http->get('id')) {
            $currency = new Currency();
            $currencyData['code'] = $currency->getCurrencyCode();
            $currencyData['currency'] = $currency->getCurrency();
            $site_url = get_site_url();

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
            $wp_userDetails = ManageUser::getInstance()->getUserDetails($http->get('id'));
            if($wp_userDetails->data->user_login){
                $wp_userD['user_login'] = $wp_userDetails->data->user_login;
            } else {
                $wp_userD = array();
            }
            return view('@Axisubs/Admin/customers/edit.twig', compact('pagetitle', 'item', 'currencyData', 'site_url', 'wp_userD'));
        } else {
            $item = array();
            $newuser = 1;
            $newCustomersSelectBox = Customers::loadNewUsersNotInCustomersSelectbox();
            $pagetitle = 'Add Customer';
            return view('@Axisubs/Admin/customers/edit.twig', compact('pagetitle', 'item', 'currencyData', 'site_url', 'wp_userD', 'newCustomersSelectBox', 'newuser'));
        }

        return $this->index();
    }

    /**
     * delete
     * */
    public function delete()
    {
        $http = Http::capture();
        if($http->get('id')) {
            $result = Customers::deleteCustomer($http->get('id'));
            if($result){
                Notifier::success('Customer deleted successfully');
            } else {
                Notifier::error('Failed to delete');
            }
        }

        return $this->index();
    }

    /**
     * Load customer subscription - for ajax request
     * */
    public function loadCustomerSubscriptions(){
        $http = Http::capture();
        $id = $http->get('id');
        $items = Customers::loadSubscriptionsByUserId($id);
        return view('@Axisubs/Admin/customers/moresubscriptions.twig', compact('items'));
    }

    /**
     * Load Customer Details for auto populate
     * */
    public function loadCustomerDetails(){
        $http = Http::capture();
        $id = $http->get('id');
        $result = Customers::loadCustomerDetailsByUserId($id);
        echo json_encode($result);
    }

    /**
     * Add customer through ajax
     * */
    public function addCustomer(){
        $http = Http::capture();
        $result = Customers::addNewCustomer($http->all());
        echo json_encode($result);
    }
}