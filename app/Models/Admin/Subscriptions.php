<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace Axisubs\Models\Admin;
use Axisubs\Helper;
use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
use Axisubs\Models\Site\Plans;
use Events\Event;
use Axisubs\Helper\ManageUser;
use Axisubs\Helper\AxisubsRedirect;

class Subscriptions extends Post{
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
            Subscriptions::$_start = $post['limitstart'];
        } else {
            Subscriptions::$_start = 0;
        }
        if(isset($post['limit']) && $post['limit']){
            Subscriptions::$_limit = $post['limit'];
        } else {
            Subscriptions::$_limit = 10;
        }
    }

    public static function getPaginationStartAndLimit($total = 0){
        Subscriptions::$_total = $total;
        $balance = Subscriptions::$_total-(Subscriptions::$_limit*Subscriptions::$_start);
        if($balance < Subscriptions::$_limit){
            $limit = $balance;
        } else {
            $limit = Subscriptions::$_limit;
        }
        $result['start'] = Subscriptions::$_start;
        $result['limit'] = $limit;

        return $result;
    }


    // Load all Subscriptions
    public static function all($columns = ['*']){
        $postO = new Post();
        //$totalItem = Post::where('post_type', 'axisubs_subscribe')->orderBy('ID','desc')->get();
        $totalItem = $postO->all()->where('post_type', 'axisubs_subscribe')->sortBy('ID');

        //get pagination start and limit
        $pageLimit = Subscriptions::getPaginationStartAndLimit(count($totalItem));
        //get limited data
        $items = $totalItem->forPage($pageLimit['start'], $pageLimit['limit']);

        if(count($items)){
            foreach ($items as $key => $item){
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
                if(isset($item->meta[$item->ID.'_axisubs_subscribe_plan_id'])) {
                    $plan = Subscriptions::loadPlan($item->meta[$item->ID . '_axisubs_subscribe_plan_id']);
                    $item->plan = $plan;
                } else {
                    unset($items[$key]);
                }

            }
        }
        $items = $items->sortByDesc('ID');
        return $items;
    }

    //get Total
    public static function getTotal(){
        $postO = new Post();
        $totalItem = $postO->all()->where('post_type', 'axisubs_subscribe');
        return count($totalItem);
    }

    //Load Single Subscriber
    public static function loadSubscriber($id){
        $item = Post::where('post_type', 'axisubs_subscribe')->find($id);
        if($item) {
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $item->meta = $meta;
        }
        return $item;
    }

    //load plan
    public static function loadPlan($id){
        $plan = Plans::loadPlan($id, 1);
        return $plan;
    }

    /**
     * For deleting a Subscription
     * */
    public static function deleteSubscriptions($id){
        $postDB = Post::where('post_type', 'axisubs_subscribe')->find($id);
        if(!empty($postDB)) {
            //On before delete trigger
            Event::trigger('onBeforeSubscriptionDelete', array($id));

            //Remove user Role
            $subscriptionPrefix = '_axisubs_subscribe_';
            $planKey = $postDB->ID . $subscriptionPrefix . 'plan_id';
            $userKey = $postDB->ID . $subscriptionPrefix . 'user_id';
            $plan = Subscriptions::loadPlan($postDB->meta->$planKey, 1);
            $planPrefix = '_axisubs_plans_';
            $removeRoleskey = $plan->ID . $planPrefix . 'remove_roles';
            if ($plan->meta[$removeRoleskey] != '') {
                ManageUser::getInstance()->removeUserRole($postDB->meta->$userKey, explode(',', $plan->meta[$removeRoleskey]));
            }

            $postDB->meta()->delete();
            $result = $postDB->delete();
            if ($result) {
                Event::trigger('onAfterSubscriptionDelete', array($id));
            }
            return $result;
        } else {
            AxisubsRedirect::redirect('?page=subscriptions-index');
        }
    }

    /**
     * For activating a Subscription
     * */
    public static function activateSubscriptions($id){
        $subscription = Subscriptions::loadSubscriber($id);
        if(!empty($subscription)) {
            $subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
            if($subscription->meta[$subscriptionPrefix.'status'] == 'PENDING'){
                return Plans::getInstance()->markActive($subscription->ID);
            }
        }
        AxisubsRedirect::redirect('?page=subscriptions-index');
    }

    /**
     * For cancel a Subscription
     * */
    public static function cancelSubscriptions($id){
        $subscription = Subscriptions::loadSubscriber($id);
        if(!empty($subscription)) {
            $subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
            if($subscription->meta[$subscriptionPrefix.'status'] != 'CANCELED'){
                return Plans::getInstance()->markCancel($subscription->ID);
            }
        }
        AxisubsRedirect::redirect('?page=subscriptions-index');
    }

    /**
     * For mark as pending
     * */
    public static function pendingSubscriptions($id){
        $subscription = Subscriptions::loadSubscriber($id);
        if(!empty($subscription)) {
            $subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
            if($subscription->meta[$subscriptionPrefix.'status'] != 'PENDING'){
                return Plans::getInstance()->markPending($subscription->ID);
            }
        }
        AxisubsRedirect::redirect('?page=subscriptions-index');
    }

    public static function loadPlanSelectbox($selected = '', $name = 'axisubs_plan', $id = 'axisubs_plan'){
        $plans = Plans::getItems(0);
        if(count($plans)){
            $select = '<select name="'.$name.'" id="'.$id.'" class="required" onchange="autoPopulatePlanDetails(this.value);">';
            $select .= '<option value="">Select Plan</option>';
            foreach ($plans as $key => $plan){
                if($selected == $plan->ID){
                    $selectedText = ' selected="selected"';
                } else {
                    $selectedText = '';
                }
                $planMeta = $plan->meta;
                $planPrefix = $plan->ID.'_'.$plan->post_type.'_';
                $select .= '<option value="'.$plan->ID.'"'.$selectedText.'>'.$planMeta[$planPrefix.'name'].'</option>';
            }
            $select .= '</select>';
        } else {
            $select = 'No Plans';
        }

        return $select;
    }
    
    public function addSubscription($user_id, $plan_id, $sub_id = 0, $start_on = ''){
        $planObj = Plans::getInstance();
        $result = $planObj->addSubscriptionThroughBackend($user_id, $plan_id, $sub_id, $start_on);
        return $result;
    }
}