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
class User extends Post{
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

    public static function LoginUser($post)
    {
        global $error;

        if(isset($post['axisubs']['user'])){
            $userLogin = $post['axisubs']['user'];
            if($userLogin['user_name']!= '' && $userLogin['password']!= ''){
                $user = wp_authenticate($userLogin['user_name'], $userLogin['password']);
                if ( ! is_wp_error($user) ) {
                    //$creds = array();
                    ob_start(wp_signon( array('user_login' => $userLogin['user_name'], 'user_password' => $userLogin['password']), ''))
                    ;
                    //wp_signon();
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
//        $user = wp_authenticate($post, $password);
//
//        if ( ! is_wp_error($user) )
//            return true;
//
//        $error = $user->get_error_message();
//        return false;
        return $item;
    }
}