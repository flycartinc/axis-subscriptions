<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace Axisubs\Models\Admin;

use Events\Event;
use Corcel\Post;
use Axisubs\Helper\DateFormat;
use Herbert\Framework\Models\PostMeta;

class Dashboard extends Post{
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

    /**
     * To get Today Sale
     * */
    public static function getTodaySale(){
        $dateObj = DateFormat::getInstance();
        $today = $dateObj->getOnlyDate();
        $valid = PostMeta::where('meta_key','like','%_axisubs_subscribe_created_on')
            ->where('meta_value', 'like', $today.'%')
            ->pluck('post_id');

        foreach($valid as $key => $value) {
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $subsPrifix = $item->ID.'_'.$item->post_type.'_';
            $statusKey = $subsPrifix.'status';
            $valid[$key] = $item;
            if($item->meta[$statusKey] == 'FUTURE' || $item->meta[$statusKey] == 'ACTIVE' || $item->meta[$statusKey] == 'TRIAL'){
            } else {
                unset($valid[$key]);
            }
        }
        return $valid;
    }

    /**
     * To get Total Sale price till date
     * */
    public static function getTotalSalePrice(){
        $valid = PostMeta::where('meta_key','like','%_axisubs_subscribe_status')
            ->whereIn('meta_value', array('ACTIVE', 'EXPIRED', 'FUTURE'))
            ->pluck('post_id');
        $total = 0;
        foreach($valid as $key => $value) {
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $subsPrifix = $item->ID.'_'.$item->post_type.'_';
            $totalpriceKey = $subsPrifix.'total_price';
            $valid[$key] = $item;
            if(isset($item->meta[$totalpriceKey])){
                $total += $item->meta[$totalpriceKey];
            }
        }
        return $total;
    }

    /**
     * To get Total pending / OrderPage(new)
     * */
    public static function getTotalPending(){
        $valid = PostMeta::where('meta_key','like','%_axisubs_subscribe_status')
            ->whereIn('meta_value', array('PENDING', 'ORDER_PAGE'))
            ->pluck('post_id');
        return $valid;
    }
}