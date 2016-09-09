<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

class ManageUser
{
    public static $instance = null;

    /**
     * get an instance
     * */
    public static function getInstance(array $config = array())
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Get WP User Details
     * */
    public function getUserDetails($id = 0)
    {
        //For access get user function
        if (is_admin()) require_once(ABSPATH . 'wp-includes/pluggable.php');

        if($id) {
            $user = get_userdata($id);
        } else {
            $user = wp_get_current_user();
        }
        return $user;
    }

    /**
     * Add role for an user
     * */
    public function addUserRole($id, $roles){
        if($id && (is_array($roles) || $roles != '')) {
            $user = $this->getUserDetails($id);
            if (is_array($roles)) {
                foreach ($roles as $role) {
                    $user->add_role($role);
                }
            } else {
                $user->add_role($roles);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add role for an user
     * */
    public function removeUserRole($id, $roles){
        if($id && (is_array($roles) || $roles != '')) {
            $user = $this->getUserDetails($id);
            if(is_array($roles)){
                foreach ($roles as $role){
                    $user->remove_role($role);
                }
            } else {
                $user->remove_role($roles);
            }
            return true;
        } else {
            return false;
        }
    }
}