<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;
use Axisubs\Helper\DateFormat;
use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
use Axisubs\Models\Site\Plans;

class ExpiryControl
{
    /**
     * Process Subscription Expiry
     * */
    public function processSubscriptionExpiry(){
        $this->processActiveSubscriptions();
        $this->processFutureSubscriptions();
        $this->processTrialSubscriptions();
    }

    /**
     * Process Active subsbscription to expire if end date matches
     * */
    protected function processActiveSubscriptions(){
        $planObject = new Plans();
        $date = DateFormat::getInstance();
        $subscribers = PostMeta::where('meta_key','like','%_axisubs_subscribe_status')
            ->where('meta_value', 'ACTIVE')
            ->pluck('post_id');

        $subscriptionPrefix = '_axisubs_subscribe_';
        $today = $date->getOnlyDate();
        $todayTime = new \DateTime($today);
        foreach($subscribers as $key => $value){
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $endKey = $value.$subscriptionPrefix.'end_on';
//            $statusKey = $value.$subscriptionPrefix.'status';

            if($meta[$endKey] != '-' && $meta[$endKey] != '0000-00-00 00:00:00'){
                $endDate = $date->getOnlyDate($meta[$endKey]);
                $endTime = new \DateTime($endDate);
                if($endTime<$todayTime){
//                    $item->meta->$statusKey = 'EXPIRED';
//                    $item->save();
                    $planObject->markExpired($item);
                }
            }
        }

    }

    /**
     * Process Future subscription to make ACTIVE if date matches
     * */
    protected function processFutureSubscriptions(){
        $planObject = new Plans();
        $date = DateFormat::getInstance();
        $subscribers = PostMeta::where('meta_key','like','%_axisubs_subscribe_status')
            ->where('meta_value', 'FUTURE')
            ->pluck('post_id');

        $subscriptionPrefix = '_axisubs_subscribe_';
        $today = $date->getOnlyDate();
        $todayTime = new \DateTime($today);
        foreach($subscribers as $key => $value){
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $startKey = $value.$subscriptionPrefix.'start_on';
            $endDate = $date->getOnlyDate($meta[$startKey]);
            $endTime = new \DateTime($endDate);
            if($endTime == $todayTime){
                $planObject->markActive($item);
            }
        }
    }

    /**
     * Process Trial subscription to end trial if date matches
     * */
    protected function processTrialSubscriptions(){
        $planObject = new Plans();
        $date = DateFormat::getInstance();
        $subscribers = PostMeta::where('meta_key','like','%_axisubs_subscribe_status')
            ->where('meta_value', 'TRIAL')
            ->pluck('post_id');

        $subscriptionPrefix = '_axisubs_subscribe_';
        $today = $date->getOnlyDate();
        $todayTime = new \DateTime($today);
        foreach($subscribers as $key => $value){
            $item = Post::where('post_type', 'axisubs_subscribe')->find($value);
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $endKey = $value.$subscriptionPrefix.'trial_end_on';
            $endDate = $date->getOnlyDate($meta[$endKey]);
            $endTime = new \DateTime($endDate);
            if($endTime == $todayTime){
                $planObject->markActive($item);
            }
        }
    }
}