<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace AxisubsAppCoupon\Models\Admin;

use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
use Herbert\Framework\Http;
use Axisubs\Models\Site\Plans;
use Axisubs\Helper\AxisubsRedirect;
use Axisubs\Helper\DateFormat;

class AxisubsAppCoupon extends Post
{
    public $_folder = 'axisubs-app-coupon';
    /**
     * The table associated with the model.
     *
     * @var string
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $_item = array();

    public static $post_type = 'axisubs_coupon';

    public static $post_type_items = 'axisubs_coupons';

    public static $_total;
    public static $_start;
    public static $_limit;

    public function __construct(array $attributes = [])
    {
    }

    public static function populateStates($post){
        if(isset($post['limitstart']) && $post['limitstart']){
            AxisubsAppCoupon::$_start = $post['limitstart'];
        } else {
            AxisubsAppCoupon::$_start = 0;
        }
        if(isset($post['limit']) && $post['limit']){
            AxisubsAppCoupon::$_limit = $post['limit'];
        } else {
            AxisubsAppCoupon::$_limit = 10;
        }
    }

    public static function getPaginationStartAndLimit($total = 0){
        AxisubsAppCoupon::$_total = $total;
        $balance = AxisubsAppCoupon::$_total-(Plans::$_limit*Plans::$_start);
        if($balance < AxisubsAppCoupon::$_limit){
            $limit = $balance;
        } else {
            $limit = AxisubsAppCoupon::$_limit;
        }
        $result['start'] = AxisubsAppCoupon::$_start;
        $result['limit'] = $limit;

        return $result;
    }

    /**
     * Get coupons
     * */
    public function getCoupons(){
        $postO = new Post();
        $totalItem = $postO->where('post_type', AxisubsAppCoupon::$post_type_items)->get();
        //get pagination start and limit
        $pageLimit = AxisubsAppCoupon::getPaginationStartAndLimit(count($totalItem));
        //get limited data
        $items = $totalItem->forPage($pageLimit['start'], $pageLimit['limit']);
        if(count($items)){
            foreach ($items as $key => $item) {
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            }
        }
        return $items;
    }

    /**
     * Get single coupons
     * */
    public function getCoupon($id){
        $item = Post::where('post_type', AxisubsAppCoupon::$post_type_items)->find($id);
        if($item) {
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
        }
        return $item;
    }

    /**
     * Save a coupon
     * */
    public static function saveCoupon($post){
        if(isset($post['id']) && $post['id']){
            $postDB = Post::where('post_type', AxisubsAppCoupon::$post_type_items)->get();
            $postTable = $postDB->find($post['id']);
        } else {
            $postTable = new Post();
            $postTable->post_name = 'Axisubs Coupon';
            $postTable->post_title = 'Axisubs Coupon';
            $postTable->post_type = AxisubsAppCoupon::$post_type_items;
            $postTable->save();
        }

        foreach ($post['axisubs']['coupon'] as $key => $val) {
            $key = $postTable->ID . '_'.$postTable->post_type.'_' . $key;
            if(is_array($val)){
                $postTable->meta->$key = implode(',', $val);
            } else {
                $postTable->meta->$key = $val;
            }
        }
        $result = $postTable->save();
        if($result){
            return $postTable->ID;
        } else {
            return false;
        }
    }

    /**
     * Delete Coupon
     * */
    public static function deleteCoupon($id){
        if($id){
            $postDB = Post::where('post_type', AxisubsAppCoupon::$post_type_items)->get();
            $postTable = $postDB->find($id);
            if(!empty($postTable)){
                $postTable->meta()->delete();
                return $postTable->delete();
            } else {
                AxisubsRedirect::redirect('?page=app-index&task=view&p=axisubs-app-coupon');
            }
        } else {
            return false;
        }
    }

    /**
     * Validate Coupon
     * */
    public function validateCouponCode($couponCode, $plan = array(), $subscription = array(), $total_price = 0){
        $date = DateFormat::getInstance();
        $today = $date->getCarbonDate();
        $couponPostId = PostMeta::where('meta_key','like','%_axisubs_coupons_code')
            ->where('meta_value', $couponCode)
            ->pluck('post_id')->first();
        $codeActive = true;

        if($couponPostId){
            $coupon = $this->getCoupon($couponPostId);
            $couponPrefix = $coupon->ID.'_'.$coupon->post_type.'_';
            if($coupon->meta[$couponPrefix.'status'] == "1") {
                if ($coupon->meta[$couponPrefix . 'valid_from'] != '' && strtotime($coupon->meta[$couponPrefix . 'valid_from']) > strtotime($today)) {
                    $codeActive = false;
                }
                if ($coupon->meta[$couponPrefix . 'valid_to'] != '' && strtotime($coupon->meta[$couponPrefix . 'valid_to']) < strtotime($today)) {
                    $codeActive = false;
                }
                if ($total_price) {
                    $couponValue = $this->calculateDiscountToValidate($total_price, $couponCode);

                    if ($couponValue <= 0) {
                        $codeActive = false;
                    }
                }
                Session()->set('axisubs_coupon_code', $couponCode);
            } else {
                $codeActive = false;
            }
        } else {
            $codeActive = false;
        }
        if($codeActive){
            Session()->set('axisubs_coupon_code', $couponCode);
        } else {
            Session()->set('axisubs_coupon_code', null);
        }
        return $codeActive;
    }

    /**
     * Calculate discount for validate
     * */
    public function calculateDiscountToValidate($totalPrice, $couponCode){
        $amount = 0;
        if($couponCode) {
            $coupon = $this->couponByCouponCode($couponCode);
            if($coupon){
                $couponPrefix = $coupon->ID.'_'.$coupon->post_type.'_';
                if ($coupon->meta[$couponPrefix.'value_type'] == 'percent') {
                    $amount = round((($coupon->meta[$couponPrefix.'value'] / 100) * $totalPrice), 2);
                } else if ($coupon->meta[$couponPrefix.'value_type'] == 'fixed') {
                    $amount = round($coupon->meta[$couponPrefix.'value'], 2);
                } else {
                    $amount = 0;
                }
                if ($amount >= $totalPrice) {
                    $amount = 0;
                }
            }
        }
        return $amount;
    }

    /**
     * Calculate discount
     * */
    public function calculateDiscount($plan){
        $couponCode = Session()->get('axisubs_coupon_code', null);
        $amount = 0;
        if($couponCode) {
            if (isset($plan->meta['total_price']) && $plan->meta['total_price'] > 0) {
                if(isset($plan->meta['total_excluding_discount']) && $plan->meta['total_excluding_discount'] > 0){
                    $totalPrice = $plan->meta['total_excluding_discount'];
                } else {
                    $totalPrice = $plan->meta['total_price'];
                }
                $couponvalidate = $this->validateCouponCode($couponCode);
                if($couponvalidate){
                    $coupon = $this->couponByCouponCode($couponCode);
                    if($coupon){
                        $couponPrefix = $coupon->ID.'_'.$coupon->post_type.'_';
                        if ($coupon->meta[$couponPrefix.'value_type'] == 'percent') {
                            $amount = round((($coupon->meta[$couponPrefix.'value'] / 100) * $totalPrice), 2);
                        } else if ($coupon->meta[$couponPrefix.'value_type'] == 'fixed') {
                            $amount = round($coupon->meta[$couponPrefix.'value'], 2);
                        } else {
                            $amount = 0;
                        }
                        if ($amount >= $totalPrice) {
                            $amount = 0;
                        }
                    }
                }
            }
        }
        return $amount;
    }

    /**
     * get coupon by coupon code
     * */
    public function couponByCouponCode($couponCode){
        $couponPostId = PostMeta::where('meta_key','like','%_axisubs_coupons_code')
            ->where('meta_value', $couponCode)
            ->pluck('post_id')->first();
        if($couponPostId){
            $coupon = $this->getCoupon($couponPostId);
        } else {
            $coupon = false;
        }

        return $coupon;
    }

    /**
     * get coupon by coupon code
     * */
    public function checkCouponCodeExists($couponCode, $id){
        $coupon = $this->couponByCouponCode($couponCode);
        if($coupon) {
            if ($id) {
                if ($coupon->ID == $id) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}