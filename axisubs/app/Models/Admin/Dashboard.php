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

    /**
     * get last five subscriptions
     * */
    public static function getLastFiveSubscriptions(){
        $postO = new Post();
        //$totalItem = Post::where('post_type', 'axisubs_subscribe')->orderBy('ID','desc')->get();
        $totalItem = $postO->all()->where('post_type', 'axisubs_subscribe')->sortByDesc('ID');
        $items = $totalItem->forPage(1, 5);

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
        return $items;
    }

    /**
     * Get subscription in between
     * */
    public static function getSubscriptionInBetween($from, $to, $all = false){
        $subscriptionIDs = PostMeta::where('meta_key','like','%_axisubs_subscribe_created_on')
            ->where('meta_value', '>=', $from)
            ->where('meta_value', '<=', $to)
            ->pluck('post_id');
        $subscriptions = array();
        foreach($subscriptionIDs as $key => $value) {
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $subsPrefix = $item->ID.'_'.$item->post_type.'_';
            $statusKey = $subsPrefix.'status';
            if($all){
                $subscriptions[$value] = new \stdClass();
                $subscriptions[$value] = $item;
            } else {
                if(in_array($item->meta[$statusKey], array('ACTIVE', 'EXPIRED', 'FUTURE'))){
                    $subscriptions[$value] = new \stdClass();
                    $subscriptions[$value] = $item;
                }
            }
        }

        return $subscriptions;
    }

    /**
     * Get last year statistics
     * */
    public static function getLastYearStatistics(){
        $dateObj = DateFormat::getInstance();
        $lastYear = date('Y')-1;
        $lastYearFrom = $dateObj->getCarbonDate(date($lastYear.'-01-01 00:00:00'));
        $lastYearTo = $dateObj->getCarbonDate(date('Y-01-01 00:00:00'));
        $subscription = Dashboard::getStatisticsInBetween($lastYearFrom, $lastYearTo);

        return $subscription;
    }

    /**
     * Get this year statistics
     * */
    public static function getThisYearStatistics(){
        $dateObj = DateFormat::getInstance();
        $thisYearFrom = $dateObj->getCarbonDate(date('Y-01-01 00:00:00'));
        $thisYearTo = $dateObj->getCarbonDate(date('Y-m-d H:i:s'));
        $subscription = Dashboard::getStatisticsInBetween($thisYearFrom, $thisYearTo);

        return $subscription;
    }

    /**
     * Get last month statistics
     * */
    public static function getLastMonthStatistics(){
        $dateObj = DateFormat::getInstance();
        if(date('m') == "01"){
            $lastMonthFrom = $dateObj->getCarbonDate(date((date('Y')-1).'-12-01 00:00:00'));
            $lastMonthTo = $dateObj->getCarbonDate(date((date('Y')-1).'-12-31 00:00:00'));
        } else {
            $lastMonth = ((int)date('m'))-1;
            if(count_chars($lastMonth) == 1){
                $lastMonth = '0'.$lastMonth;
            }
            $lastMonthFrom = $dateObj->getCarbonDate(date('Y-'.$lastMonth.'-01 00:00:00'));
            $lastMonthTo = $dateObj->getCarbonDate(date('Y-'.$lastMonth.'-31 00:00:00'));
        }
        $subscription = Dashboard::getStatisticsInBetween($lastMonthFrom, $lastMonthTo);

        return $subscription;
    }

    /**
     * Get this month statistics
     * */
    public static function getThisMonthStatistics(){
        $dateObj = DateFormat::getInstance();

        $thisMonthFrom = $dateObj->getCarbonDate(date('Y-m-01 00:00:00'));
        $thisMonthTo = $dateObj->getCarbonDate(date('Y-m-d H:i:s'));
        $subscription = Dashboard::getStatisticsInBetween($thisMonthFrom, $thisMonthTo);

        return $subscription;
    }

    /**
     * Get last 7 days statistics
     * */
    public static function getLastSevenDaysStatistics(){
        $dateObj = DateFormat::getInstance();
        $startDate = $dateObj->getCarbonDate(date ("Y-m-d 00:00:00", strtotime("-7 day", strtotime(date('Y-m-d 00:00:00')))));
        $endDate = $dateObj->getCarbonDate(date('Y-m-d H:i:s'));
        $subscription = Dashboard::getStatisticsInBetween($startDate, $endDate);

        return $subscription;
    }

    /**
     * Get last yesterdays statistics
     * */
    public static function getYesterdayStatistics(){
        $dateObj = DateFormat::getInstance();
        $startDate = $dateObj->getCarbonDate(date ("Y-m-d 00:00:00", strtotime("-1 day", strtotime(date('Y-m-d 00:00:00')))));
        $endDate = $dateObj->getCarbonDate(date("Y-m-d H:i:s", strtotime("+1 day", strtotime($startDate))));
        $subscription = Dashboard::getStatisticsInBetween($startDate, $endDate);

        return $subscription;
    }

    /**
     * Get last today statistics
     * */
    public static function getTodayStatistics(){
        $dateObj = DateFormat::getInstance();
        $startDate = $dateObj->getCarbonDate(date ("Y-m-d 00:00:00"));
        $endDate = $dateObj->getCarbonDate(date("Y-m-d H:i:s"));
        $subscription = Dashboard::getStatisticsInBetween($startDate, $endDate);

        return $subscription;
    }

    /**
     * Get total active subscriptions
     * */
    public static function getTotalActiveSubscriptions(){
        $subscriptions = PostMeta::where('meta_key','like','%_axisubs_subscribe_status')
            ->where('meta_value', '=', 'ACTIVE')
            ->pluck('post_id');

        return count($subscriptions);
    }

    /**
     * Get statistics in between dates
     * */
    public static function getStatisticsInBetween($from, $to, $all = false){
        $betweenSubscriptions = Dashboard::getSubscriptionInBetween($from, $to, $all);
        $subscription['total'] = count($betweenSubscriptions);
        $subscription['total_income'] = 0;
        if(count($betweenSubscriptions)){
            foreach($betweenSubscriptions as $key => $betweenSubscription) {
                $subsPrefix = $betweenSubscription->ID.'_'.$betweenSubscription->post_type.'_';
                $total_price_key = $subsPrefix.'total_price';
                $subscription['total_income'] = $subscription['total_income']+$betweenSubscription->meta[$total_price_key];
            }
        }
        return $subscription;
    }
}