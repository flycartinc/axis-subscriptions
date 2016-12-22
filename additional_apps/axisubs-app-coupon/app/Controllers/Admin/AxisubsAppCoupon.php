<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace AxisubsAppCoupon\Controllers\Admin;

use AxisubsAppCoupon\Controllers\Controller;
use AxisubsAppCoupon\Models\Admin\AxisubsAppCoupon as AxisubsAppCouponModel;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper\Common;
use Axisubs\Models\Admin\Config;
use Axisubs\Helper\Pagination;
use Axisubs\Helper\Currency;

class AxisubsAppCoupon extends Controller
{
    public $_controller = 'AxisubsAppCoupon';
    public $_element = 'app_coupon';
    public $_folder = 'axisubs-app-payment-authorizenet';

    /**
     * Default App view
     * */
    public static function loadAppView(){
        $currentObject = new AxisubsAppCoupon();
        $currentObject->index();
    }

    /**
     * Load coupon form
     * */
    public static function loadCouponForm($plan, $subscription){
        $couponCode = Session()->get('axisubs_coupon_code', null);
        $data = view('@AxisubsAppCoupon/Site/Form/form.twig', compact('plan', 'couponCode'));
        if($data->getStatusCode() == '200'){
            return $data->getBody();
        } else {
           // echo "Something goes wrong";
        }
    }

    /**
     * Load coupon item
     * */
    public static function loadCouponDetails($plan, $subscriptions, $page){
        $couponCode = Session()->get('axisubs_coupon_code', null);
        $modelObject = new AxisubsAppCouponModel();
        $item = $modelObject->couponByCouponCode($couponCode);
        $discountValue = $modelObject->calculateDiscount($plan);
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $data['page'] = $page;
        $data['currencyData'] = $currencyData;
        $dataHtml = view('@AxisubsAppCoupon/Site/List/list.twig', compact('item', 'couponCode', 'discountValue', 'data'));
        if($dataHtml->getStatusCode() == '200'){
            return $dataHtml->getBody();
        } else {
            // echo "Something goes wrong";
        }
    }

    /**
     * Default layout
     * */
    public function index()
    {
        $pagetitle = "Coupons";
        $model = $this->getModel();
        $http = Http::capture();
        AxisubsAppCouponModel::populateStates($http->all());
        $items = $model->getCoupons();
        $pagination = new Pagination(AxisubsAppCouponModel::$_start, AxisubsAppCouponModel::$_limit, AxisubsAppCouponModel::$_total);
        $paginationD['limitbox'] = $pagination->getLimitBox();
        $paginationD['links'] = $pagination->getPaginationLinks();

        $data = view('@AxisubsAppCoupon/Admin/List/default.twig', compact('pagetitle', 'items', 'paginationD'));
        if($data->getStatusCode() == '200'){
            echo $data->getBody();
        } else {
            echo "Something goes wrong";
        }
    }

    /**
     * Default layout
     * */
    public function edit(){
        $pagetitle = "Add Coupon";
        $http = Http::capture();
        $model = $this->getModel();
        $item = array();
        if ($http->get('id')) {
            $item = $model->getCoupon($http->get('id'));
            if (!empty($item)) {
                $pagetitle = 'Edit Coupon';
            }
        }
        $site_url = get_site_url();
        $data = view('@AxisubsAppCoupon/Admin/Form/edit.twig', compact('pagetitle', 'item', 'site_url'));
        if($data->getStatusCode() == '200'){
            echo $data->getBody();
        } else {
            echo "Something goes wrong";
        }
    }

    /**
     * Save coupon
     * */
    public function save(){
        $http = Http::capture();
        $axisubPost = $http->get('axisubs');
        $item = array();
        if (isset($axisubPost['coupon'])) {
            $result = AxisubsAppCouponModel::saveCoupon($http->all());
            if ($result) {
                Notifier::success('Saved successfully');
                $model = $this->getModel();
                $item = $model->getCoupon($result);
                if (!empty($item)) {
                    $pagetitle = 'Edit Coupon';
                }
                $site_url = get_site_url();
                $data = view('@AxisubsAppCoupon/Admin/Form/edit.twig', compact('pagetitle', 'item', 'site_url'));
                if($data->getStatusCode() == '200'){
                    echo $data->getBody();
                } else {
                    echo "Something goes wrong";
                }
            } else {
                Notifier::error('Failed to save');
                return $this->index();
            }
        }
    }

    /**
     * Delete Coupon
     * */
    public function delete(){
        $http = Http::capture();
        if ($http->get('id')) {
            $model = $this->getModel();
            $result = $model->deleteCoupon($http->get('id'));
            if ($result) {
                Notifier::success('Deleted successfully');
            } else {
                Notifier::error('Failed to delete');
            }
        }
        return $this->index();
    }

    /**
     * Apply Coupon
     * */
    public function applyCoupon($plan, $subscription){
        $http = Http::capture();
        if($http->get('coupon_code', '') != '') {
            $model = $this->getModel();
            $total_price = $http->get('price', '');
            if($total_price == ''){
                if(isset($plan->meta['total_price'])){
                    $total_price = $plan->meta['total_price'];
                } else {
                    $total_price = 0;
                }
            }
            $status = $model->validateCouponCode($http->get('coupon_code', ''), $plan, $subscription, $total_price);
            if($status){
                $result['status'] = 200;
                $result['message'] = 'Coupon applied successfully';
            } else {
                $result['status'] = 0;
                $result['message'] = 'Invalid coupon code';
            }
        } else {
            $result['status'] = 0;
            $result['message'] = 'Please enter coupon code';
        }
        echo json_encode($result);
    }

    /**
     * Apply Coupon
     * */
    public function removeCoupon(){
        Session()->set('axisubs_coupon_code', null);
        $result['status'] = 200;
        $result['message'] = 'Coupon applied successfully';
        echo json_encode($result);
    }

    /**
     * Calculate Discount
     * */
    public function calculateDiscount($plan){
        $model = $this->getModel();
        return $model->calculateDiscount($plan);
    }

    /**
     * Clear Session variables
     * */
    public static function clearSession(){
        Session()->set('axisubs_coupon_code', null);
    }

    /**
     * load price list in subscription page
     * */
    public function loadCouponPriceListInSubscriptionPage($subscription){
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $data['currencyData'] = $currencyData;
        $dataHtml = view('@AxisubsAppCoupon/Site/Subscription/price.twig', compact('subscription', 'data'));
        if($dataHtml->getStatusCode() == '200'){
            return $dataHtml->getBody();
        } else {
            // echo "Something goes wrong";
        }
    }

    /**
     * Check coupon code already exist
     * */
    public function checkCouponCodeExist(){
        $http = Http::capture();
        $couponCode = $http->get('coupon_code');
        $model = $this->getModel();
        $result = $model->checkCouponCodeExists($couponCode, $http->get('id', 0));
        if($result){
            echo 1;
        } else {
            echo 0;
        }
    }
}