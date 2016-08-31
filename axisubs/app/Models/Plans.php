<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace Axisubs\Models;
use Axisubs\Helper;
use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
use Axisubs\Helper\Duration;

class Plans extends Post{
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

    public static $_total;
    public static $_start;
    public static $_limit;

    public static function populateStates($post){
        if(isset($post['limitstart']) && $post['limitstart']){
            Plans::$_start = $post['limitstart'];
        } else {
            Plans::$_start = 0;
        }
        if(isset($post['limit']) && $post['limit']){
            Plans::$_limit = $post['limit'];
        } else {
            Plans::$_limit = 10;
        }
    }

    public static function getPaginationStartAndLimit($total = 0){
        Plans::$_total = $total;
        $balance = Plans::$_total-(Plans::$_limit*Plans::$_start);
        if($balance < Plans::$_limit){
            $limit = $balance;
        } else {
            $limit = Plans::$_limit;
        }
        $result['start'] = Plans::$_start;
        $result['limit'] = $limit;

        return $result;
    }

    // Load all Plans
    public static function all($columns = ['*']){
//        $items = parent::all()->where('post_type', 'axisubs_plans');
        $postO = new Post();
        $totalItem = $postO->all()->where('post_type', 'axisubs_plans');
        //get pagination start and limit
        $pageLimit = Plans::getPaginationStartAndLimit(count($totalItem));
        //get limited data
        $items = $totalItem->forPage($pageLimit['start'], $pageLimit['limit']);

        if(count($items)){
            foreach ($items as $key => $item){
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            }
        }

        return $items;
    }

    //Load plans for front end
    public static function allFrontEndPlans(){
        $items = parent::all()->where('post_type', 'axisubs_plans');
        /*$postO = new Post();
        $items = $postO->where('post_type', 'axisubs_plans')->meta()->where('meta_key','like','%_axisubs_plans_status')
            ->where('meta_value',1)->get();
        foreach ($items as $key=>$val){
            if($key == 'ID'){
                $valid = Post::type('axisubs_plans')->meta()->where('');
            }
        }

        $valid = PostMeta::where('meta_key','like','%_axisubs_plans_status')
            ->where('meta_value',1)
            ->pluck('post_id');
        dd($items);*/

        if(count($items)){
            foreach ($items as $key => $item){
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
                if($item->meta[$item->ID.'_axisubs_plans_status'] == "0"){
                    unset($items[$key]);
                }
            }
        }

        return $items;
    }

    //Load Single Plan
    public static function loadPlan($id){
        $item = Post::all()->where('post_type', 'axisubs_plans')->find($id);
        if($item) {
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $meta['allow_setupcost'] = 1;
            if(isset($meta[$item->ID.'_axisubs_plans_price']) && isset($meta[$item->ID.'_axisubs_plans_price'])) {
                $meta['total_price'] = $meta[$item->ID . '_axisubs_plans_price'] + $meta[$item->ID . '_axisubs_plans_setup_cost'];
            } else if(isset($meta[$item->ID.'_axisubs_plans_price'])){
                $meta['total_price'] = $meta[$item->ID . '_axisubs_plans_price'];
            } else {
                $meta['total_price'] = 0;
            }
            $item->meta = $meta;
        }
        return $item;
    }

    // save Plans
    public static function deletePlan($id){
        if($id){
            $postDB = Post::where('post_type', 'axisubs_plans')->get();
            $postTable = $postDB->find($id);
            $postTable->meta()->delete();
            return $postTable->delete();
        } else {
            return false;
        }
    }

    // save Plans
    public static function savePlans($post){
        if($post['id']){
            $postDB = Post::where('post_type', 'axisubs_plans')->get();
            $postTable = $postDB->find($post['id']);
        } else {
            $postTable = new Post();
            $postTable->post_name = 'plans';
            $postTable->post_title = 'Plans';
            $postTable->post_type = 'axisubs_plans';
            $postTable->save();
        }

        foreach ($post['axisubs']['plans'] as $key => $val) {
            $key = $postTable->ID . '_axisubs_plans_' . $key;
            if(is_array($val)){
                $postTable->meta->$key = implode(',', $val);
            } else {
                $postTable->meta->$key = $val;
            }
        }

        $forever = $postTable->ID . '_axisubs_plans_period_forever';
        if(!isset($post['axisubs']['plans']['period_forever'])){
            $postTable->meta->$forever = '0';
        }
        $result = $postTable->save();
        if($result){
            return $postTable->ID;
        } else {
            return false;
        }
    }
    // update Subscribe
    public static function updateSubscribe($post, $plans){
        $sessionData = Session()->get('axisubs_subscribers');
        if(isset($sessionData[$plans->ID]) && $sessionData[$plans->ID]->subscriberId){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->get();
            $postTable = $postDB->find($sessionData[$plans->ID]->subscriberId);
            $key = $sessionData[$plans->ID]->subscriberId.'_axisubs_subscribe_status';
            $postTable->meta->$key = 'ACTIVE';
            $result = $postTable->save();
            if($result){
                Session()->set('axisubs_subscribers', null);
                return $postTable->ID;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // get All subscriptions
    public static function loadAllSubscribes($id = 0){
        if($id){
            $userId = $id;
        } else {
            $userId =get_current_user_id();   
        }        
        $subscribers = PostMeta::where('meta_key','like','%_axisubs_subscribe_user_id')
            ->where('meta_value', $userId)->orderBy('post_id','desc')
            ->pluck('post_id');

        foreach($subscribers as $key => $value){
            $today = date("Y-m-d g:i:s");
            $item = Post::all()->where('post_type', 'axisubs_subscribe')->find($value);
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $plan = Plans::loadPlan($item->meta[$item->ID.'_axisubs_subscribe_plan_id']);
            $item->plan = $plan;
            $subscribers[$key] = $item;
        }
        return $subscribers;
    }

    //Get user Details
    public static function getUserDetails(){
        $user = Helper::getUserDetails();
        if($user->ID){
            $item = Post::all()->where('post_type', 'axisubs_user_'.$user->ID)->first();
            if($item) {
                $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
                $item->meta = $meta;
            }
            return $item;
        } else {
            return array();
        }
    }

    //Update User Details
    public static function updateUserDetails($data, $user_id = 0){
        $user = Helper::getUserDetails($user_id);
        $postDB = Post::where('post_type', 'axisubs_user_'.$user->ID)->get();
        $postTable = $postDB->first();
        if(empty($postTable)){
            $postTable = new Post();
            $postTable->post_name = 'Users';
            $postTable->post_title = 'Users';
            $postTable->post_type = 'axisubs_user_'.$user->ID;
            $postTable->save();
        }
        foreach ($data as $key => $val) {
            $key = $postTable->ID . '_axisubs_user_' .$user->ID.'_'.$key;
            if(is_array($val)){
                $postTable->meta->$key = implode(',', $val);
            } else {
                $postTable->meta->$key = $val;
            }
        }
        //for adding wordpress user_id
        $user_id_key = $postTable->ID . '_axisubs_user_' .$user->ID.'_user_id';
        $postTable->meta->$user_id_key = $user->ID;
        $result = $postTable->save();

        return $result;
    }

    // save Subscribe
    public static function addSubscribe($post, $plans){
        $sessionData = Session()->get('axisubs_subscribers');
        if(isset($sessionData[$plans->ID]) && $sessionData[$plans->ID]->subscriberId){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->get();
            $postTable = $postDB->find($sessionData[$plans->ID]->subscriberId);
        } else {
            $postTable = new Post();
            $postTable->post_name = 'Subscribers';
            $postTable->post_title = 'Subscribers';
            $postTable->post_type = 'axisubs_subscribe';
            $postTable->save();
        }

        foreach ($post['axisubs']['subscribe'] as $key => $val) {
            $key = $postTable->ID . '_axisubs_subscribe_' . $key;
            if(is_array($val)){
                $postTable->meta->$key = implode(',', $val);
            } else {
                $postTable->meta->$key = $val;
            }
        }
        //For storing User details
        Plans::updateUserDetails($post['axisubs']['subscribe']);

        $existAlready = Plans::getSubscribedDetails($plans->ID);
        if(isset($plans->meta[$plans->ID.'_axisubs_plans_price']) && $plans->meta[$plans->ID.'_axisubs_plans_price'] > 0){
            $price = $plans->meta[$plans->ID.'_axisubs_plans_price'];
        } else {
            $price = 0;
        }

        $now = date("Y-m-d g:i:s");
        if(count($existAlready)){
            $startDate = Plans::getEndDateOfSubscriber($existAlready);
            $setup_cost = 0;
        } else {
            $startDate = $now;
            if(isset($plans->meta[$plans->ID.'_axisubs_plans_setup_cost']) && $plans->meta[$plans->ID.'_axisubs_plans_setup_cost'] > 0){
                $setup_cost = $plans->meta[$plans->ID.'_axisubs_plans_setup_cost'];
            } else {
                $setup_cost = 0;
            }

        }
        //Calculate End Date
        $endDate = Plans::calculateEndDate($startDate, $plans);
        $totalCost = $price+$setup_cost;

        $extraFields = array('_axisubs_subscribe_plan_id' => $post['id'],
            '_axisubs_subscribe_status' => 'ORDER_PAGE',
            '_axisubs_subscribe_created_on' => $now,
            '_axisubs_subscribe_start_on' => $startDate,
            '_axisubs_subscribe_end_on' => $endDate,
            '_axisubs_subscribe_user_id' => get_current_user_id(),
            '_axisubs_subscribe_price' => $price,
            '_axisubs_subscribe_setup_cost' => $setup_cost,
            '_axisubs_subscribe_total_price' => $totalCost,
            '_axisubs_subscribe_payment_type' => "",
            '_axisubs_subscribe_payment_status' => "");

        foreach ($extraFields as $key1 => $val1) {
            $key1 = $postTable->ID . $key1;
            $postTable->meta->$key1 = $val1;
        }
        //dd($postTable->meta);

        $result = $postTable->save();
        if($result){
            $sessionData = Session()->get('axisubs_subscribers');
            $sessionObj = new \stdClass();
            $sessionObj->planId = $plans->ID;
            $sessionObj->subscriberId = $postTable->ID;
            $sessionData[$plans->ID] = $sessionObj;
            Session()->set('axisubs_subscribers', $sessionData);
            return $postTable->ID;
        } else {
            return false;
        }
    }

    //get End Date
    public static function calculateEndDate($startDate, $plan){
        $planSufix = $plan->ID.'_axisubs_plans_';
        $plantype = $plan->meta[$planSufix.'type'];
        $planPeriod = 0;
        $planPeriodUnit = 'D';
        if(isset($plan->meta[$planSufix.'period']) && $plan->meta[$planSufix.'period']){
            $planPeriod = $plan->meta[$planSufix.'period'];
        }
        if(isset($plan->meta[$planSufix.'period_units']) && $plan->meta[$planSufix.'period_units']){
            $planPeriodUnit = $plan->meta[$planSufix.'period_units'];
        }
        $duration = new Duration();
        if($plantype == 'free'){
            if($plan->meta[$planSufix.'period_forever'] == '1'){
                return '-';
            } else {
                $days = $duration->getDurationInDays($planPeriod, $planPeriodUnit);
            }
        } else{
            $days = $duration->getDurationInDays($planPeriod, $planPeriodUnit);
        }

        return date("Y-m-d g:i:s", strtotime($startDate." +".$days." days"));
    }

    //get end date from previous subscriber
    public static function getEndDateOfSubscriber($subscribers){
        $newEndDate = date("Y-m-d g:i:s");
        foreach($subscribers as $key => $value){
            $endDateKey = $value->ID.'_axisubs_subscribe_end_on';
            $endDate = $value->meta[$endDateKey];
            $oldDate = new \DateTime($newEndDate);
            $newDate = new \DateTime($endDate);
            if($newDate>$oldDate){
                $newEndDate = $endDate;
            }
        }
        return $newEndDate;
    }

    //load current(users) valid subscriber
    public static function getSubscribedDetails($planId){
        $userId =get_current_user_id();
        $valid = PostMeta::where('meta_key','like','%_axisubs_subscribe_user_id')
            ->where('meta_value', $userId)
            ->pluck('post_id');

        foreach($valid as $key => $value){
            $today = date("Y-m-d g:i:s");
            $item = Post::all()->where('post_type', 'axisubs_subscribe')->find($value);
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $valid[$key] = $item;
            $planIdKey = $item->ID.'_axisubs_subscribe_plan_id';
            $statusKey = $item->ID.'_axisubs_subscribe_status';
            $endDateKey = $item->ID.'_axisubs_subscribe_end_on';
            if($item->meta[$planIdKey] == $planId
                && ($item->meta[$statusKey] == 'FUTURE' || $item->meta[$statusKey] == 'ACTIVE')){
//                $endDate = $item->meta[$endDateKey];
//                $oldDate = new \DateTime($today);
//                $newDate = new \DateTime($endDate);
            } else {
                unset($valid[$key]);
            }
        }

        return $valid;

    }

    //Load Single Subscriber
    public static function loadSubscriber($id){
        $item = Post::all()->where('post_type', 'axisubs_subscribe')->find($id);
        if($item) {
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $item->meta = $meta;
        }
        return $item;
    }

    //Get oldSubscriber
    public static function loadOldSubscriber($plans)
    {
        $sessionData = Session()->get('axisubs_subscribers');
        //$sid = Session()->get('axisubs_sid');
        if (isset($sessionData[$plans->ID]) && $sessionData[$plans->ID]->subscriberId)
            $item = Plans::loadSubscriber($sessionData[$plans->ID]->subscriberId);
        else
            $item = array();
        return $item;
    }

    public static function isEligible($plan){
        $planSufix = $plan->ID.'_axisubs_plans_';
        $plantype = $plan->meta[$planSufix.'type'];
        if($plantype == 'free' || $plantype == 'non_renewal' || $plantype == 'recurring' || $plantype == 'recurring_with_trial'){
            $available = Plans::getSubscribedDetails($plan->ID);
            if(count($available)){
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}