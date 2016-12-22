<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace Axisubs\Models\Site;
use Axisubs\Helper;
use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
use Axisubs\Helper\Duration;
use Axisubs\Helper\ManageUser;
use Axisubs\Helper\AxisubsRedirect;
use Axisubs\Models\Admin\Customers;
use Axisubs\Helper\DateFormat;
use Events\Event;
use Axisubs\Controllers\Controller;
use Axisubs\Helper\Apps;
use Axisubs\Helper\Common;

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
    public $additionalPrice = array();

    public static $_total;
    public static $_start;
    public static $_limit;
    public static $instance = null;

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

    public static function getInstance(array $config = array())
    {
        if (!self::$instance)
        {
            self::$instance = new self($config);
        }

        return self::$instance;
    }
    /**
     * Load all plans with pagination
     * */
    public static function getItems($pagination = 1){
        $postO = new Post();
        $totalItem = $postO->all()->where('post_type', 'axisubs_plans');
        if($pagination){
            //get pagination start and limit
            $pageLimit = Plans::getPaginationStartAndLimit(count($totalItem));
            //get limited data
            $items = $totalItem->forPage($pageLimit['start'], $pageLimit['limit']);
        } else {
            $items = $totalItem;
        }
        

        if(count($items)){
            foreach ($items as $key => $item){
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            }
        }

        return $items;
    }

    //get Total
    public static function getTotal(){
        $postO = new Post();
        $totalItem = $postO->all()->where('post_type', 'axisubs_plans');
        return count($totalItem);
    }

    /**
     * Load plans for front end
     * */
    public static function allFrontEndPlans($ids = ''){
        $countrollerOb = new Controller();
        $items = parent::all()->where('post_type', 'axisubs_plans');

        $selectedIds = array();
        if($ids != ''){
            $selectedIds = explode(',', $ids);
        }

        if(count($items)){
            foreach ($items as $key => $item){
                $unsetRow = 0;
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
                $item->plan_url = $countrollerOb->getAxiSubsURLs('plan', 'view', $item->ID, $item->meta[$item->ID.'_axisubs_plans_slug']);
                if($item->meta[$item->ID.'_axisubs_plans_status'] == "0"){
                    unset($items[$key]);
                    $unsetRow = 1;
                }
                if(!empty($selectedIds)){
                    if(!in_array($item->ID, $selectedIds)){
                        unset($items[$key]);
                        $unsetRow = 1;
                    }
                }
                if(!$unsetRow) {
                    $item = Plans::loadAddtionalPrices($item);
                }
            }
        }

        return $items;
    }

    /**
     * Load Single Plan
     * */
    public static function loadPlan($id, $backend = 0){
        $item = Post::where('post_type', 'axisubs_plans')->find($id);
        if($item) {
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $item = Plans::loadAddtionalPrices($item, $backend);
        }
        return $item;
    }

    /**
     * To load additional price like tax/coupon
     * */
    public static function loadAddtionalPrices($item, $backend = 0){
        $countrollerOb = new Controller();
        $meta = $item->meta;
        if($backend){
            $meta['allow_setupcost'] = 1;//count(Plans::getSubscribedDetails($item->ID))? 0 : 1;
        } else {
            $meta['allow_setupcost'] = count(Plans::getSubscribedDetails($item->ID))? 0 : 1;
            $item->plan_url = $countrollerOb->getAxiSubsURLs('plan', 'view', $item->ID, $meta[$item->ID.'_axisubs_plans_slug']);
        }
        $plansObj = Plans::getInstance();
        if(isset($meta[$item->ID.'_axisubs_plans_price']) && isset($meta[$item->ID.'_axisubs_plans_setup_cost']) && $meta['allow_setupcost']) {
            $plansObj->price = $meta[$item->ID . '_axisubs_plans_price'];
            $plansObj->setup_cost = $meta[$item->ID . '_axisubs_plans_setup_cost'];
        } else if(isset($meta[$item->ID.'_axisubs_plans_price'])){
            $plansObj->price = $meta[$item->ID . '_axisubs_plans_price'];
            $plansObj->setup_cost = 0;
        } else {
            $plansObj->price = 0;
            $plansObj->setup_cost = 0;
        }
        $totalCost = $plansObj->getTotalPrice();
        $meta['total_price'] = $totalCost;
        $meta['original_price'] = $totalCost;
        $item->meta = $meta;
        $plansObj->additionalPrice($item);
        return $item;
    }

    /**
     * Delete Plans
     * */
    public static function deletePlan($id){
        if($id){
            $postDB = Post::where('post_type', 'axisubs_plans')->get();
            $postTable = $postDB->find($id);
            if(!empty($postTable)){
                $postTable->meta()->delete();
                return $postTable->delete();
            } else {
                AxisubsRedirect::redirect('?page=plans-index');
            }
        } else {
            return false;
        }
    }

    /**
     * save Plans
     * */
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

    /**
     * update Subscribe
     * */
    public static function updateFreeSubscribe($post, $plans){
        $plantype = $plans->meta[$plans->ID.'_axisubs_plans_type'];
        $sessionData = Session()->get('axisubs_subscribers');
        if(isset($sessionData[$plans->ID]) && $sessionData[$plans->ID]->subscriberId && $plantype == 'free'){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->get();
            $postTable = $postDB->find($sessionData[$plans->ID]->subscriberId);
//            $key = $sessionData[$plans->ID]->subscriberId.'_axisubs_subscribe_status';
//            $postTable->meta->$key = 'ACTIVE';
//            $result = $postTable->save();
            // Mark as Active
            $result = Plans::getInstance()->markActive($postTable);
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

    /**
     * get All subscriptions
     * */
    public static function loadAllSubscribes($id = 0){
        if($id){
            $userId = $id;
        } else {
            $userId = get_current_user_id();
            $countrollerOb = new Controller();
        }        
        $subscribers = PostMeta::where('meta_key','like','%_axisubs_subscribe_user_id')
            ->where('meta_value', $userId)->orderBy('post_id','desc')
            ->pluck('post_id');
        //get pagination start and limit
        $pageLimit = Plans::getPaginationStartAndLimit(count($subscribers));
        //get limited data
        $subscribers = $subscribers->forPage($pageLimit['start'], $pageLimit['limit']);

        foreach($subscribers as $key => $value){
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
            if(!$id){
                $item->subscription_url = $countrollerOb->getAxiSubsURLs('subscribe', 'view', $value);
            }
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $plan = Plans::loadPlan($item->meta[$item->ID.'_axisubs_subscribe_plan_id']);
            $item->plan = $plan;
            $subscribers[$key] = $item;
        }
        return $subscribers;
    }

    /**
     * get Plan By slug
     * */
    public static function getPlanBySlug($slug){
        $plans = PostMeta::where('meta_key','like','%_axisubs_plans_slug')
            ->where('meta_value', $slug)->orderBy('post_id','desc')
            ->pluck('post_id');

        foreach($plans as $key => $value){
            $item = Post::where('post_type', 'axisubs_plans')->find($value);
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
//            $plans[$key] = $item;
        }
        return $item;
    }

    /**
     * Get user Details
     * */
    public static function getUserDetails(){
        $user = ManageUser::getInstance()->getUserDetails();
        if($user->ID){
            $item = Post::where('post_type', 'axisubs_user_'.$user->ID)->first();
            if($item) {
                $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
                $item->meta = $meta;
            }
            return $item;
        } else {
            return array();
        }
    }

    /**
     * Update User Details
     * */
    public static function updateUserDetails($data, $user_id = 0){
        $user = ManageUser::getInstance()->getUserDetails($user_id);
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

    /**
     * For calculating total
     * */
    public function getTotalPrice(){
        $this->total = $this->price + $this->setup_cost;
        return $this->total;
    }

    /**
     * Add Subscription
     * */
    public function addSubscribe($post, $plans){
        $dateFormat = DateFormat::getInstance();
        $sessionData = Session()->get('axisubs_subscribers');
        $postTable = array();
        if(isset($sessionData[$plans->ID]) && $sessionData[$plans->ID]->subscriberId){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->get();
            $postTable = $postDB->find($sessionData[$plans->ID]->subscriberId);
        }
        if(empty($postTable)){
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
        Common::setCustomerDetailsInSession();
        $plans = Plans::loadPlan($plans->ID);

        //check already has active or future subscriptions
        $existAlready = Plans::getSubscribedDetails($plans->ID);

        if(isset($plans->meta[$plans->ID.'_axisubs_plans_price']) && $plans->meta[$plans->ID.'_axisubs_plans_price'] > 0){
            $price = $plans->meta[$plans->ID.'_axisubs_plans_price'];
        } else {
            $price = 0;
        }

        $now = $dateFormat->getCarbonDate();//date("Y-m-d g:i:s");
        $extraFieldsTrial = array();
        if(count($existAlready)){
            $startDate = Plans::getEndDateOfSubscriber($existAlready);
            $setup_cost = 0;
        } else {
            $startDate = $now;
            $planType = $plans->meta[$plans->ID.'_axisubs_plans_type'];
            if($planType == 'renewal_with_trial' || $planType == 'recurring_with_trial'){
                $endDate = Plans::calculateEndDate($startDate, $plans, 1);
                $extraFieldsTrial = array('_axisubs_subscribe_trial_start_on' => $startDate,
                    '_axisubs_subscribe_trial_end_on' => $endDate);
                $startDate = $endDate;
            }
            if(isset($plans->meta[$plans->ID.'_axisubs_plans_setup_cost']) && $plans->meta[$plans->ID.'_axisubs_plans_setup_cost'] > 0){
                $setup_cost = $plans->meta[$plans->ID.'_axisubs_plans_setup_cost'];
            } else {
                $setup_cost = 0;
            }

        }

        //Calculate End Date
        $endDate = Plans::calculateEndDate($startDate, $plans);

        $this->price = $price;
        $this->setup_cost = $setup_cost;
        $this->existAlready = $existAlready;

        //calculate Total price
        if(isset($plans->meta['total_price']) && $plans->meta['total_price']>0){
            $totalCost = $plans->meta['total_price'];
        } else {
            $totalCost = $this->getTotalPrice();//$price+$setup_cost;
        }

        $payment_type = '';
        if(isset($post['payment'])){
            $payment_type = $post['payment'];
        }

        $extraFields = array('_axisubs_subscribe_plan_id' => $post['id'],
            '_axisubs_subscribe_status' => 'ORDER_PAGE',
            '_axisubs_subscribe_created_on' => $now,
            '_axisubs_subscribe_start_on' => $startDate,
            '_axisubs_subscribe_end_on' => $endDate,
            '_axisubs_subscribe_user_id' => get_current_user_id(),
            '_axisubs_subscribe_price' => $price,
            '_axisubs_subscribe_setup_cost' => $setup_cost,
            '_axisubs_subscribe_total_price' => $totalCost,
            '_axisubs_subscribe_payment_type' => $payment_type,
            '_axisubs_subscribe_payment_status' => "");

        if(isset($plans->additionalPrice) && !empty($plans->additionalPrice)){
            foreach ($plans->additionalPrice as $apKey => $additionalPrice) {
                $extraFields['_axisubs_subscribe_'.$apKey] = $additionalPrice;
            }
        }

        $extraFields = array_merge($extraFields, $extraFieldsTrial);

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
            $sessionData['current_subscription_id'] = $postTable->ID;
            Session()->set('axisubs_subscribers', $sessionData);
            return $postTable->ID;
        } else {
            return false;
        }
    }

    //add subscription through backend
    public function addSubscriptionThroughBackend($user_id, $plan_id, $sub_id, $subs_startDate = ''){
        $dateFormat = DateFormat::getInstance();
        $postTable = array();
        $planchanged = 0;
        if($sub_id){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->get();
            $postTable = $postDB->find($sub_id);
        }
        if(empty($postTable)){
            $postTable = new Post();
            $postTable->post_name = 'Subscribers';
            $postTable->post_title = 'Subscribers';
            $postTable->post_type = 'axisubs_subscribe';
            $postTable->save();
        }
        // save only for new subscription
        if(!$sub_id) {
            $customer = Customers::loadCustomer($user_id);
            $customerMeta = $customer->meta;
            $customerPrefix = $customer->ID . '_' . $customer->post_type . '_';
            foreach ($customerMeta as $key => $val) {
                $field = explode($customerPrefix, $key);
                if (isset($field['1'])) {
                    $newkey = $postTable->ID . '_axisubs_subscribe_' . $field['1'];
                    $postTable->meta->$newkey = $val;
                }
            }
        } else {
            $subscripSufix = $postTable->ID.'_'.$postTable->post_type.'_';
            if($plan_id != $postTable->meta->$subscripSufix.'plan_id'){
                $planchanged = 1;
            }
        }

        $plans = Plans::loadPlan($plan_id, 1);
        //check already has active or future subscriptions
        $existAlready = Plans::getSubscribedDetails($plan_id, $user_id);

        if(isset($plans->meta[$plans->ID.'_axisubs_plans_price']) && $plans->meta[$plans->ID.'_axisubs_plans_price'] > 0){
            $price = $plans->meta[$plans->ID.'_axisubs_plans_price'];
        } else {
            $price = 0;
        }

        $now = $dateFormat->getCarbonDate();
        $extraFieldsTrial = array();
        if(count($existAlready)){
            if($subs_startDate == '') {
                $startDate = Plans::getEndDateOfSubscriber($existAlready);
            } else {
                $startDate = $dateFormat->getCarbonDate($subs_startDate);
            }
            $setup_cost = 0;
        } else {
            if($subs_startDate == ''){
                $startDate = $now;    
            } else {
                $startDate = $dateFormat->getCarbonDate($subs_startDate);
            }
            
            $planType = $plans->meta[$plans->ID.'_axisubs_plans_type'];
            if($planType == 'renewal_with_trial' || $planType == 'recurring_with_trial'){
                $endDate = Plans::calculateEndDate($startDate, $plans, 1);
                $extraFieldsTrial = array('_axisubs_subscribe_trial_start_on' => $startDate,
                    '_axisubs_subscribe_trial_end_on' => $endDate);
                $startDate = $endDate;
            }
            if(isset($plans->meta[$plans->ID.'_axisubs_plans_setup_cost']) && $plans->meta[$plans->ID.'_axisubs_plans_setup_cost'] > 0){
                $setup_cost = $plans->meta[$plans->ID.'_axisubs_plans_setup_cost'];
            } else {
                $setup_cost = 0;
            }

        }
        //Calculate End Date
        $endDate = Plans::calculateEndDate($startDate, $plans);

        $this->price = $price;
        $this->setup_cost = $setup_cost;
        $this->existAlready = $existAlready;

        //calculate Total price
        $totalCost = $this->getTotalPrice();//$price+$setup_cost;

        if(!$sub_id) {
            $extraFields = array('_axisubs_subscribe_plan_id' => $plan_id,
                '_axisubs_subscribe_status' => 'PENDING',
                '_axisubs_subscribe_created_on' => $now,
                '_axisubs_subscribe_start_on' => $startDate,
                '_axisubs_subscribe_end_on' => $endDate,
                '_axisubs_subscribe_user_id' => $user_id,
                '_axisubs_subscribe_price' => $price,
                '_axisubs_subscribe_setup_cost' => $setup_cost,
                '_axisubs_subscribe_total_price' => $totalCost,
                '_axisubs_subscribe_payment_type' => '',
                '_axisubs_subscribe_payment_status' => "");
        } else {
            if($planchanged){
                $extraFields = array('_axisubs_subscribe_plan_id' => $plan_id,
                    '_axisubs_subscribe_start_on' => $startDate,
                    '_axisubs_subscribe_end_on' => $endDate,
                    '_axisubs_subscribe_price' => $price,
                    '_axisubs_subscribe_setup_cost' => $setup_cost,
                    '_axisubs_subscribe_total_price' => $totalCost);
            } else {
                $extraFields = array('_axisubs_subscribe_plan_id' => $plan_id,
                    '_axisubs_subscribe_start_on' => $startDate,
                    '_axisubs_subscribe_end_on' => $endDate);
            }

        }

        $extraFields = array_merge($extraFields, $extraFieldsTrial);

        foreach ($extraFields as $key1 => $val1) {
            $key1 = $postTable->ID . $key1;
            $postTable->meta->$key1 = $val1;
        }

        $result = $postTable->save();
        if($result){
            return $postTable->ID;
        } else {
            return false;
        }
    }

    /**
     * get End Date
     * */
    public static function calculateEndDate($startDate, $plan, $trial = 0){
        if($trial){
            $periodField = 'trial_period';
            $periodUnitField = 'trial_period_units';
        } else {
            $periodField = 'period';
            $periodUnitField = 'period_units';
        }
        $planSufix = $plan->ID.'_axisubs_plans_';
        $plantype = $plan->meta[$planSufix.'type'];
        $planPeriod = 0;
        $planPeriodUnit = 'D';
        if(isset($plan->meta[$planSufix.$periodField]) && $plan->meta[$planSufix.$periodField]){
            $planPeriod = $plan->meta[$planSufix.$periodField];
        }
        if(isset($plan->meta[$planSufix.$periodUnitField]) && $plan->meta[$planSufix.$periodUnitField]){
            $planPeriodUnit = $plan->meta[$planSufix.$periodUnitField];
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
        $dateFormat = DateFormat::getInstance();
        $endDate = $dateFormat->getCarbonDate(date("Y-m-d g:i:s", strtotime($startDate." +".$days." days")));
        return $endDate;
    }

    /**
     * get end date from previous subscriber
     * */
    public static function getEndDateOfSubscriber($subscribers){
        $dateFormat = DateFormat::getInstance();
        $newEndDate = $dateFormat->getCarbonDate();
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

    /**
     * load current(users) has valid subscriber
     * */
    public static function getSubscribedDetails($planId, $user_id = 0){
        if($user_id){
            $userId = $user_id;
        } else {
            $userId = get_current_user_id();
        }
        $valid = PostMeta::where('meta_key','like','%_axisubs_subscribe_user_id')
            ->where('meta_value', $userId)
            ->pluck('post_id');

        foreach($valid as $key => $value){
            $today = date("Y-m-d g:i:s");
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
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

    /**
     * Load Single Subscriber
     * */
    public static function loadSubscriber($id){
        $item = Post::where('post_type', 'axisubs_subscribe')->find($id);
        if($item) {
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $item->meta = $meta;
        }
        return $item;
    }

    /**
     * Get oldSubscriber
     * */
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

    /**
     * check for active subscriptions
     * */
    public function checkForActiveSubscription($subscription)
    {
        $dateFormat = DateFormat::getInstance();
        $subscription = Plans::loadSubscriber($subscription->ID);
        $subsPrefix = $subscription->ID.'_axisubs_subscribe_';
        $subscriptions = Plans::getSubscribedDetails($subscription->meta[$subsPrefix.'plan_id'], $subscription->meta[$subsPrefix.'user_id']);
        $newEndDate = $dateFormat->getCarbonDate();//date("Y-m-d g:i:s");

        $lastSubscription = array();
        foreach($subscriptions as $key => $value){
            if($subscription->ID == $value->ID){
                continue;
            }
            $endDateKey = $value->ID.'_axisubs_subscribe_end_on';
            $endDate = $value->meta[$endDateKey];
            $oldDate = new \DateTime($newEndDate);
            $newDate = new \DateTime($endDate);
            if($newDate>$oldDate){
                $newEndDate = $endDate;
                $lastSubscription = $value;
            }
        }
        return $lastSubscription;
    }

    /**
     * get Next Renewal
     * */
    public function getNextRenewal($subscription_id, $trans_id = ''){
        if($trans_id != ''){
            $oldSubscrption = Plans::loadSubscriber($subscription_id);
            $subsPrefix = $oldSubscrption->ID.'_'.$oldSubscrption->post_type.'_';
            if((!isset($oldSubscrption->meta[$subsPrefix.'transaction_ref_id']) || $oldSubscrption->meta[$subsPrefix.'transaction_ref_id'] == '')){
                return $subscription_id;
            }
            $subs = $this->checkForTransactionId($trans_id);
        } else {
            $subs = 0;
        }
        if($subs){
            return $subs;
        } else {
            return $this->createACopyOfSubscription($subscription_id);
        }
    }

    /**
     * create a copy of subscription
     * */
    public function createACopyOfSubscription($subscription_id){
        $dateFormat = DateFormat::getInstance();
        $oldSubscription = Plans::loadSubscriber($subscription_id);
        $oldSubscriptionPrefix = $oldSubscription->ID.'_axisubs_subscribe_';
        $planId = $oldSubscription->meta[$oldSubscriptionPrefix.'plan_id'];

        $plans = Plans::loadPlan($planId);

        $existAlready = Plans::getSubscribedDetails($plans->ID);
        if(isset($plans->meta[$plans->ID.'_axisubs_plans_price']) && $plans->meta[$plans->ID.'_axisubs_plans_price'] > 0){
            $price = $plans->meta[$plans->ID.'_axisubs_plans_price'];
        } else {
            $price = 0;
        }

        $now = $dateFormat->getCarbonDate();//date("Y-m-d g:i:s");
        $setup_cost = 0;
        if(count($existAlready)){
            $startDate = Plans::getEndDateOfSubscriber($existAlready);
        } else {
            $startDate = $now;
        }
        //Calculate End Date
        $endDate = Plans::calculateEndDate($startDate, $plans);
        $totalCost = $price+$setup_cost;

        $postTable = new Post();
        $postTable->post_name = 'Subscribers';
        $postTable->post_title = 'Subscribers';
        $postTable->post_type = 'axisubs_subscribe';
        $postTable->save();

        $newSubscriptionPrefix = $postTable->ID.'_axisubs_subscribe_';
        $fieldsToBeFilled = array('first_name', 'city', 'province', 'phone', 'plan_id',
            'last_name', 'pincode', 'user_id',
            'email', 'country', 'payment_type',
            'address_line1', 'address_line2');
        foreach ($oldSubscription->meta as $key => $val) {
            $split = explode($oldSubscriptionPrefix, $key);
            $fieldName = $split['1'];
            if(in_array($fieldName, $fieldsToBeFilled)){
                $newKey = $newSubscriptionPrefix.$fieldName;
                $postTable->meta->$newKey = $val;
            }
        }

        $extraFields = array('_axisubs_subscribe_plan_id' => $planId,
            '_axisubs_subscribe_status' => 'PENDING',
            '_axisubs_subscribe_created_on' => $now,
            '_axisubs_subscribe_start_on' => $startDate,
            '_axisubs_subscribe_end_on' => $endDate,
            '_axisubs_subscribe_price' => $price,
            '_axisubs_subscribe_setup_cost' => $setup_cost,
            '_axisubs_subscribe_total_price' => $totalCost,
            '_axisubs_subscribe_payment_status' => "");

        foreach ($extraFields as $key1 => $val1) {
            $key1 = $postTable->ID . $key1;
            $postTable->meta->$key1 = $val1;
        }

        $result = $postTable->save();
        if($result){
            return $postTable->ID;
        } else {
            return false;
        }
    }

    /**
     * Check for transaction ID
     * */
    public function checkForTransactionId($trans_id){
        $subscription = PostMeta::where('meta_key','like','%_axisubs_subscribe_transaction_ref_id')
            ->where('meta_value', $trans_id)
            ->pluck('post_id')->first();
        if (empty($subscription)){
            return false;
        } else {
            return $subscription;
        }
    }

    /**
     * Get all subscriptions based on profile Id
     * */
    public function getBySubscriptionProfileId($profile_id){
        $subscription = PostMeta::where('meta_key','like','%_axisubs_subscribe_subscription_profile_id')
            ->where('meta_value', $profile_id)
            ->pluck('post_id');
        if (empty($subscription)){
            return false;
        } else {
            foreach($subscription as $key => $value){
                $item[$key] = Post::where('post_type', 'axisubs_subscribe')->find($value);
                $item[$key]->meta = $item[$key]->meta()->pluck('meta_value', 'meta_key')->toArray();
            }

            return $item;
        }
    }

    /**
     * check eligible for subscription
     * */
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

    /**
     * Handle pending payment
     * */
    public function paymentPending($subscription_id, $transaction){
        if($subscription_id){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->find($subscription_id);
            $subsPrefix = $subscription_id.'_axisubs_subscribe_';
            if(!empty($postDB)){
                foreach ($transaction as $keyT => $val) {
                    $key = $subsPrefix.$keyT;
                    $postDB->meta->$key = $val;
                }

                $key = $subsPrefix.'payment_status';
                $postDB->meta->$key = 'PENDING';
                $key = $subsPrefix.'status';
                $postDB->meta->$key = 'PENDING';
                $result = $postDB->save();
                
                //Trigger Mail
                Event::trigger( 'mailPaymentPending', $subscription_id, 'filter');
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Handle failed payment
     * */
    public function paymentFailed($subscription_id, $transaction){
        if($subscription_id){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->find($subscription_id);
            $subsPrefix = $subscription_id.'_axisubs_subscribe_';
            if(!empty($postDB)){
                foreach ($transaction as $keyT => $val) {
                    $key = $subsPrefix.$keyT;
                    $postDB->meta->$key = $val;
                }

                $key = $subsPrefix.'payment_status';
                $postDB->meta->$key = 'FAILED';
                $key = $subsPrefix.'status';
                $postDB->meta->$key = 'FAILED';
                $result = $postDB->save();
                //Trigger Mail
                Event::trigger( 'mailPaymentFailed', $subscription_id, 'filter');
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Handle completed payment
     * */
    public function paymentCompleted($subscription_id, $transaction){
        if($subscription_id){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->find($subscription_id);
            $subsPrefix = $subscription_id.'_axisubs_subscribe_';
            if(!empty($postDB)){
                foreach ($transaction as $keyT => $val) {
                    $key = $subsPrefix.$keyT;
                    $postDB->meta->$key = $val;
                }

                $key = $subsPrefix.'payment_status';
                $postDB->meta->$key = 'SUCCESS';
                
                //Trigger Mail
                Event::trigger( 'mailPaymentCompleted', $subscription_id, 'filter');
                
                //For check to set Active or Future
                $activeSubs = $this->checkForActiveSubscription($postDB);
                $key = $subsPrefix.'status';
                if(empty($activeSubs)){
                    $dateFormat = DateFormat::getInstance();
                    $todayDate = $dateFormat->getCarbonDate();
                    $now_time = strtotime($todayDate);
                    $key_start_date = $subsPrefix.'start_on';
                    $startDate = $dateFormat->getCarbonDate($postDB->meta->$key_start_date);
                    $starttime = strtotime($startDate);
                    if($now_time>=$starttime) {
                        // Mark as Active
                        return Plans::getInstance()->markActive($postDB);
                    } else {
                        // Mark as Future
                        return Plans::getInstance()->markFuture($postDB);
                    }
                } else {
                    // Mark as Future
                    return Plans::getInstance()->markFuture($postDB);
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Handle canceled payment
     * */
    public function paymentCanceled($subscription_id, $transaction){
        if($subscription_id){
            $postDB = Post::where('post_type', 'axisubs_subscribe')->find($subscription_id);
            $subsPrefix = $subscription_id.'_axisubs_subscribe_';
            if(!empty($postDB)){
                foreach ($transaction as $keyT => $val) {
                    $key = $subsPrefix.$keyT;
                    $postDB->meta->$key = $val;
                }

                $key = $subsPrefix.'payment_status';
                $postDB->meta->$key = 'CANCELED';
                $key = $subsPrefix.'status';
                $postDB->meta->$key = 'CANCELED';
                $result = $postDB->save();
                //Trigger Mail
                Event::trigger( 'mailPaymentCanceled', $subscription_id, 'filter');
                
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }    
    
    /**
     * Mark as Expired
     * */
    public function markExpired($subscription){
        //TODO: Change this condition as required
        if((isset($subscription->ID) && $subscription->ID) && (isset($subscription->post_type) && $subscription->post_type == 'axisubs_subscribe')){
        } else {
            if((int)$subscription){
                $subscription = Post::where('post_type', 'axisubs_subscribe')->find((int)$subscription);
                if(empty($subscription)){
                    return false;
                }
            } else {
                return false;
            }
        }
        $subscriptionPrefix = '_axisubs_subscribe_';
        $statusKey = $subscription->ID.$subscriptionPrefix.'status';
        $subscription->meta->$statusKey = 'EXPIRED';

        $result = $subscription->save();
        //Trigger Mail
        Event::trigger( 'mailSubscriptionExpired', $subscription->ID, 'filter');
        
        if($result){
            //Remove user Role
            $planKey = $subscription->ID.$subscriptionPrefix.'plan_id';
            $userKey = $subscription->ID.$subscriptionPrefix.'user_id';
            $plan = Plans::loadPlan($subscription->meta->$planKey, 1);
            $planPrefix = '_axisubs_plans_';
            $removeRoleskey = $plan->ID.$planPrefix.'remove_roles';
            if($plan->meta[$removeRoleskey] != ''){
                ManageUser::getInstance()->removeUserRole($subscription->meta->$userKey, explode(',', $plan->meta[$removeRoleskey]));
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Mark as Active
     * */
    public function markActive($subscription){
        //TODO: Change this condition as required
        if((isset($subscription->ID) && $subscription->ID) && (isset($subscription->post_type) && $subscription->post_type == 'axisubs_subscribe')){
        } else {
            if((int)$subscription){
                $subscription = Post::where('post_type', 'axisubs_subscribe')->find((int)$subscription);
                if(empty($subscription)){
                    return false;
                }
            } else {
                return false;
            }
        }
        $subscriptionPrefix = '_axisubs_subscribe_';
        $statusKey = $subscription->ID.$subscriptionPrefix.'status';
        $subscription->meta->$statusKey = 'ACTIVE';
        $result = $subscription->save();

        //Trigger Mail
        Event::trigger( 'mailSubscriptionActive', $subscription->ID, 'filter');
        
        if($result){
            //Trigger after Active subscription
            $apps = new Apps();
            //Load plugin for content after
            $apps->loadHtml('runAfterActivateSubscription', $subscription->ID);

            //Add user Role
            $planKey = $subscription->ID.$subscriptionPrefix.'plan_id';
            $userKey = $subscription->ID.$subscriptionPrefix.'user_id';
            $plan = Plans::loadPlan($subscription->meta->$planKey, 1);
            $planPrefix = '_axisubs_plans_';
            $addRoleskey = $plan->ID.$planPrefix.'add_roles';
            if($plan->meta[$addRoleskey] != ''){
                ManageUser::getInstance()->addUserRole($subscription->meta->$userKey, explode(',', $plan->meta[$addRoleskey]));
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Mark as TRIAL
     * */
    public function markTrial($subscription){
        //TODO: Change this condition as required
        if((isset($subscription->ID) && $subscription->ID) && (isset($subscription->post_type) && $subscription->post_type == 'axisubs_subscribe')){
        } else {
            if((int)$subscription){
                $subscription = Post::where('post_type', 'axisubs_subscribe')->find((int)$subscription);
                if(empty($subscription)){
                    return false;
                }
            } else {
                return false;
            }
        }
        $subscriptionPrefix = '_axisubs_subscribe_';
        $statusKey = $subscription->ID.$subscriptionPrefix.'status';
        $subscription->meta->$statusKey = 'TRIAL';
        $result = $subscription->save();
        if($result){
            //Add user Role
            $planKey = $subscription->ID.$subscriptionPrefix.'plan_id';
            $userKey = $subscription->ID.$subscriptionPrefix.'user_id';
            $plan = Plans::loadPlan($subscription->meta->$planKey, 1);
            $planPrefix = '_axisubs_plans_';
            $addRoleskey = $plan->ID.$planPrefix.'add_roles';
            if($plan->meta[$addRoleskey] != ''){
                ManageUser::getInstance()->addUserRole($subscription->meta->$userKey, explode(',', $plan->meta[$addRoleskey]));
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Mark as Future
     * */
    public function markFuture($subscription){
        //TODO: Change this condition as required
        if((isset($subscription->ID) && $subscription->ID) && (isset($subscription->post_type) && $subscription->post_type == 'axisubs_subscribe')){
        } else {
            if((int)$subscription){
                $subscription = Post::where('post_type', 'axisubs_subscribe')->find((int)$subscription);
                if(empty($subscription)){
                    return false;
                }
            } else {
                return false;
            }
        }
        $subscriptionPrefix = '_axisubs_subscribe_';
        $statusKey = $subscription->ID.$subscriptionPrefix.'status';
        $subscription->meta->$statusKey = 'FUTURE';
        return $subscription->save();
    }

    /**
     * Mark as Cancel
     * */
    public function markCancel($subscription){
        //TODO: Change this condition as required
        if((isset($subscription->ID) && $subscription->ID) && (isset($subscription->post_type) && $subscription->post_type == 'axisubs_subscribe')){
        } else {
            if((int)$subscription){
                $subscription = Post::where('post_type', 'axisubs_subscribe')->find((int)$subscription);
                if(empty($subscription)){
                    return false;
                }
            } else {
                return false;
            }
        }
        $subscriptionPrefix = '_axisubs_subscribe_';
        $statusKey = $subscription->ID.$subscriptionPrefix.'status';
        $subscription->meta->$statusKey = 'CANCELED';
        return $subscription->save();
    }

    /**
     * Mark as Pending
     * */
    public function markPending($subscription){
        //TODO: Change this condition as required
        if((isset($subscription->ID) && $subscription->ID) && (isset($subscription->post_type) && $subscription->post_type == 'axisubs_subscribe')){
        } else {
            if((int)$subscription){
                $subscription = Post::where('post_type', 'axisubs_subscribe')->find((int)$subscription);
                if(empty($subscription)){
                    return false;
                }
            } else {
                return false;
            }
        }
        $subscriptionPrefix = '_axisubs_subscribe_';
        $statusKey = $subscription->ID.$subscriptionPrefix.'status';
        $subscription->meta->$statusKey = 'PENDING';
        return $subscription->save();
    }

    /**
     * Pre process the plan
     * */
    public function preProcessPlan(&$item, &$data, $subscriber, $page = ''){
        $apps = new Apps();
        //Load plugin for content after
        $content_in_price_list = $apps->loadHtml('loadContentInPriceList', array($item, $data, $subscriber, $page));
        $data['content_in_price_list'] = $content_in_price_list;
        
        //Load plugin for content after
        $content_after_price = $apps->loadHtml('loadContentAfterPrice', array($item, $data, $subscriber, $page));
        $data['content_after_price'] = $content_after_price;
    }

    /**
     * Pre process the plan
     * */
    public function preProcessBackendPlanListing(&$items, &$data){
        $apps = new Apps();
        //Load plugin for additional buttons
        $buttons = $apps->loadHtml('loadButtonInBackendPlanList', array($items, $data));
        $data['additional_buttons'] = $buttons;
    }

    /**
     * Pre process the plan edit
     * */
    public function preProcessBackendPlanEdit(&$item, &$data){
        $apps = new Apps();
        //Load plugin for additional data
        $returnData = $apps->loadHtml('loadAdditionalDataInBackendPlanEdit', array($item, $data));
        $data['payment_additional_data'] = $returnData;
    }

    /**
     * add Aditional price like coupon/tax
     * */
    public function additionalPrice(&$plan){
//        Event::trigger( 'axisubs-app-additionalPrice', $plan, 'filter');
        Event::trigger( 'axisubs-app-additionalPrice', array($plan));
    }

    /**
     * To display Aditional price like coupon/tax
     * */
    public function preProcessSubscription(&$item, &$data){
        $apps = new Apps();
        //Load plugin for content after
        $content_in_price_list = $apps->loadHtml('loadContentInSubscriptionPriceList', array($item, $data));
        $data['aditionalPrices'] = $content_in_price_list;
    }

    /**
     * get active subscriptions based on plan for an user
     * */
    public static function getActiveSubscriptionsBasedOnPlan($planId, $id = 0){
        if($id){
            $userId = $id;
        } else {
            $userId = get_current_user_id();
            $countrollerOb = new Controller();
        }
        $subscribers = PostMeta::where('meta_key','like','%_axisubs_subscribe_user_id')
            ->where('meta_value', $userId)->orderBy('post_id','desc')
            ->pluck('post_id');

        foreach($subscribers as $key => $value){
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
//            if(!$id){
//                $item->subscription_url = $countrollerOb->getAxiSubsURLs('subscribe', 'view', $value);
//            }
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
//            $plan = Plans::loadPlan($item->meta[$item->ID.'_axisubs_subscribe_plan_id']);
//            $item->plan = $plan;
            $subscriptionPrefix = $item->ID.'_'.$item->post_type.'_';
            if($item->meta[$subscriptionPrefix.'plan_id'] == $planId && $item->meta[$subscriptionPrefix.'status'] == 'ACTIVE'){
                $subscribers[$key] = $item;
            } else {
                unset($subscribers[$key]);
            }

        }
        return $subscribers;
    }

    /**
     * To get all plan Names as an array
     * */
    public static function getAllPlanNamesByMeta(){
        $plans = PostMeta::where('meta_key','like','%_axisubs_plans_name')
            ->orderBy('post_id','desc')
            ->pluck('meta_value', 'post_id')->toArray();

        return $plans;
    }

    /**
     * To display Aditional buttons like upgrade
     * */
    public function additionalButtonsInSubscription(&$subscribers){
        $apps = new Apps();
        $apps->loadHtml('loadButtonsInSubscribedList', $subscribers);
    }
}