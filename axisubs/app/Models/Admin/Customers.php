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
use Corcel\User;

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

    /**
     * For populate post values
     * */
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

    /**
     * For getting Pagination values
     * */
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

    /**
     * Load all Customers
     * */
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

    /**
     * load plan
     * */
    public static function loadPlan($id){
        $plan = Plans::loadPlan($id);
        return $plan;
    }

    /**
     * get Customer Details with subscriptions
     * */
    public static function loadCustomer($id){
        $item = Post::where('post_type', 'axisubs_user_'.$id)->first();
        if($item) {
            $meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            $item->meta = $meta;
            $item->subscription = Customers::loadSubscriptionsByUserId($id);
        }
        return $item;
    }

    /**
     * load subscription with plan based on wp userid
     * */
    public static function loadSubscriptionsByUserId($id){
        $item = Plans::loadAllSubscribes($id);
        return $item;
    }

    /**
     * For saving a customer details
     * */
    public static function saveCustomer($post, $user_id){
        if(!empty($post['axisubs']['subscribe'])){
            $result = Plans::updateUserDetails($post['axisubs']['subscribe'], $user_id);
            if($result){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * For deleting a customer
     * */
    public static function deleteCustomer($id){
        $postDB = Post::where('post_type', 'axisubs_user_'.$id)->first();
        $postDB->meta()->delete();
        return $postDB->delete();
    }

    /**
     * For loading users who doesn't in customer list
     * */
    public static function loadNewUsersNotInCustomers(){
        $data = PostMeta::where('meta_key','like','%_axisubs_user_%_user_id')
            ->pluck('meta_value')->toArray();
        $users = User::whereNotIn('ID',$data)->get();
        return $users;
    }

    /**
     * Select box of users who doesn't in customer list
     * */
    public static function loadNewUsersNotInCustomersSelectbox(){
        $customers = Customers::loadNewUsersNotInCustomers();
        if(count($customers)){
            $select = '<select name="wp_user_id" id="axisubs_wp_user_id" onchange="autoPopulateCustomerDetails(this.value);">';
            $select .= '<option value="">Select User</option>';
            foreach ($customers as $key => $customer){
                $select .= '<option value="'.$customer->ID.'">'.$customer->user_login.'</option>';
            }
            $select .= '</select>';
        } else {
            $select = 'No users';
        }

        return $select;
    }

    /**
     * Load Wordpress user details by user-id for ajax
     * */
    public static function loadCustomerDetailsByUserId($id){
        $users  = User::where('ID', '=', $id)->get()->first();
        $fields = array();
        if(!empty($users)){
            $data['status'] = 'success';
            $users->meta = $users->meta()->pluck('meta_value', 'meta_key')->toArray();
            $fields['user_login'] = $users->user_login;
            $fields['email'] = $users->user_email;
            $fields['id'] = $users->ID;
            $fields['first_name'] = $users->meta['first_name'];
            $fields['last_name'] = $users->meta['last_name'];
        } else {
            $data['status'] = 'failed';
        }
        $data['fields'] = $fields;
        return $data;
    }

    /**
     * Add new customer through ajax
     * */
    public static function addNewCustomer($post){
        if($post['add_type'] && $post['id']){
            $user_id = $post['id'];
        } else {
            $addUser = \Axisubs\Models\Site\User::registerUser($post, 1);
            if($addUser['status'] == 'success'){
                $user_id = $addUser['ID'];
            } else {
                return $addUser;
            }
        }
        $result = Customers::saveCustomer($post, $user_id);
        $resultReturn = array();
        if($result){
            $resultReturn['status'] = 'success';
            $resultReturn['message'] = 'User created successfully. We are redirecting please wait..';
            $resultReturn['ID'] = $user_id;
        } else {
            $resultReturn['status'] = 'failed';
            $resultReturn['message'] = 'User created and failed update customer details. Please try again';
        }
        return $resultReturn;
    }
}