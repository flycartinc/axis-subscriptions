<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Models\Site\Plans;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper\Pagination;
use Axisubs\Models\Admin\Customers;
use Axisubs\Controllers\Controller;
use Axisubs\Helper\Currency;
use Axisubs\Helper\PaymentPlugins;

class Plan extends Controller
{
    public $_controller = 'Plan';

    /**
     * Default page
     * */
    public function index()
    {
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $http = Http::capture();
        $pagetitle = 'Plans';
        Plans::populateStates($http->all());
        // Load Listing layout
        $items = Plans::getItems();

        //pre process the plan
        $data['additional_buttons'] = '';
        $model = $this->getModel('Plans', 'Site');
        $model->preProcessBackendPlanListing($items, $data);

        $pagination = new Pagination(Plans::$_start, Plans::$_limit, Plans::$_total);
        $paginationD['limitbox'] = $pagination->getLimitBox();
        $paginationD['links'] = $pagination->getPaginationLinks();
        return view('@Axisubs/Admin/plans/list.twig', compact('pagetitle', 'items', 'paginationD', 'currencyData', 'data'));
    }

    /**
     * Delete Plan
     * */
    public function delete(){
        $http = Http::capture();
        if ($http->get('id')) {
            $result = Plans::deletePlan($http->get('id'));
            if ($result) {
                Notifier::success('Deleted successfully');
            } else {
                Notifier::error('Failed to delete');
            }
        }
        return $this->index();
    }

    /**
     * New / Edit Plan
     * */
    public function edit(){
        $item = array();
        $http = Http::capture();
        $role_names = wp_roles()->role_names;
        $site_url = get_site_url();
        $pagetitle = 'Add new plan';
        if ($http->get('id')) {
            $item = Plans::loadPlan($http->get('id'));
            if (!empty($item)) {
                $pagetitle = 'Edit Plan';
            }
        }
        wp_enqueue_media();
        $payment = new PaymentPlugins();
        $data['plugin_url'] = AXISUBS_PLUGIN_URL;
        $data['payment_plugins'] = $payment->getAllPaymentApps();
        $model = $this->getModel('Plans', 'Site');
        $model->preProcessBackendPlanEdit($item, $data);
        return view('@Axisubs/Admin/plan/edit.twig', compact('pagetitle', 'item', 'role_names', 'site_url', 'data'));
    }

    /**
     * Save Plan
     * */
    public function save(){
        $http = Http::capture();
        $role_names = wp_roles()->role_names;
        $site_url = get_site_url();

        $axisubPost = $http->get('axisubs');
        if (isset($axisubPost['plans'])) {
            $result = Plans::savePlans($http->all());
            $pagetitle = 'Edit Plan';
            $data['plugin_url'] = AXISUBS_PLUGIN_URL;
            if ($result) {
                Notifier::success('Saved successfully');
                $item = Plans::loadPlan($result);
                $payment = new PaymentPlugins();
                $data['payment_plugins'] = $payment->getAllPaymentApps();
                $model = $this->getModel('Plans', 'Site');
                $model->preProcessBackendPlanEdit($item, $data);
                return view('@Axisubs/Admin/plan/edit.twig', compact('pagetitle', 'item', 'role_names', 'site_url', 'data'));
            } else {
                $item = $axisubPost['plans'];
                Notifier::error('Failed to save');
                return view('@Axisubs/Admin/plan/edit.twig', compact('pagetitle', 'item', 'role_names', 'site_url'));
            }
        } else {
            return $this->index();
        }
    }

    /**
     * Load plan fields based on plan type through ajax
     * */
    public function loadPlanFields(){
        $http = Http::capture();
        $planType = $http->get('type', '');
        $id = $http->get('id');
        $item = Plans::loadPlan($id);
        if($planType != ''){
            $data = view('@Axisubs/Admin/plan/types/'.$planType.'.twig', compact('item'));
        } else {
            $data = view('@Axisubs/Admin/plan/types/free.twig', compact('item'));
        }
        if($data->getStatusCode() == 200){
            echo $data->getBody();
        } else {
            echo "Something goes wrong!";
        }
    }
}