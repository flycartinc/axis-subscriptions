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

    // Load all Subscriptions
    public static function all($columns = ['*']){
        $items = parent::all()->where('post_type', 'axisubs_subscribe');
       // $items->orderBy('ID','desc');
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