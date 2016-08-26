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
use Axisubs\Models\Plans;

class Customers extends Post{
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
            Customers::$_start = $post['limitstart'];
        } else {
            Customers::$_start = 0;
        }
        if(isset($post['limit']) && $post['limit']){
            Customers::$_limit = $post['limit'];
        } else {
            Customers::$_limit = 10;
        }
    }

    public static function getPaginationStartAndLimit($total = 0){
        Customers::$_total = $total;
        $balance = Customers::$_total-(Customers::$_limit*Customers::$_start);
        if($balance < Customers::$_limit){
            $limit = $balance;
        } else {
            $limit = Customers::$_limit;
        }
        $result['start'] = Customers::$_start;
        $result['limit'] = $limit;

        return $result;
    }

    // Load all Customers
    public static function all($columns = ['*']){
        $postO = new Post();
        $totalItem = $postO->where('post_type', 'like', 'axisubs_user_%')->get();
        //get pagination start and limit
        $pageLimit = Customers::getPaginationStartAndLimit(count($totalItem));
        //get limited data
        $items = $totalItem->forPage($pageLimit['start'], $pageLimit['limit']);
        if(count($items)){
            foreach ($items as $key => $item) {
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
                if (isset($item->meta[$item->ID . '_'.$item['post_type'].'_user_id'])) {
                    $item->subscription = Plans::loadAllSubscribes($item->meta[$item->ID . '_'.$item['post_type'].'_user_id']);
                } else {
                    $item->subscription = '';
                }
            }
        }

        return $items;
    }

    //load plan
    public static function loadPlan($id){
        $plan = Plans::loadPlan($id);
        return $plan;
    }

    //get Customer Details with subscriptions
    public static function loadCustomer($id){
        $item = Post::all()->where('post_type', 'axisubs_user_'.$id)->first();
        if($item) {
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $item->meta = $meta;
            $item->subscription = Plans::loadAllSubscribes($id);
        }
        return $item;
    }

    public static function loadSubscriptionsByUserId($id){
        $item = Plans::loadAllSubscribes($id);
        return $item;
    }
}