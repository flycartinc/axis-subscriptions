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
//use Herbert\Pagination\Paginator;
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
            Subscriptions::$_limit = 5;
        }
    }
    
    // Load all Subscriptions
    public static function all($columns = ['*']){
        $postO = new Post();//parent::all();
        //$items = parent::all()->where('post_type', 'axisubs_subscribe')
        $totalItem = $postO->all()->where('post_type', 'axisubs_subscribe');
        Subscriptions::$_total = count($totalItem);
//        $items = $totalItem->forPage(2, 5);

        $items = $totalItem->forPage(Subscriptions::$_start, Subscriptions::$_limit);
        if(count($items)){
            foreach ($items as $key => $item){
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
                if(isset($item->meta[$item->ID.'_axisubs_subscribe_plan_id'])) {
                    $plan = Plans::loadPlan($item->meta[$item->ID . '_axisubs_subscribe_plan_id']);
                } else {
                    unset($items[$key]);
                }
                $item->plan = $plan;
            }
        }

        return $items;
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

    //load plan
    public static function loadPlan($id){
        $plan = Plans::loadPlan($id);
        return $plan;
    }
}