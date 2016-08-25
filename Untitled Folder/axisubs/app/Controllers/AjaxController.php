<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers;

use Axisubs\Models\Plans;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper;
use Axisubs\Models\User;

class AjaxController
{
    public function loginUser(Http $http)
    {
        $task = $http->get('task');
        if ($task == 'loginUser') {
            $result = User::loginUser($http->all());
        } else {
            $result['status'] = 'failed';
            $result['message'] = 'Something goes wrong.';
        }
        echo json_encode($result);

    }

    public function registerUser(Http $http){
        $result = User::registerUser($http->all());
        echo json_encode($result);
    }

    public function updateProfile(Http $http){
        $post = $http->all();
        $result = Plans::updateUserDetails($post['axisubs']['subscribe']);
        if($result){
            $data['status'] = 'success';
            $data['message'] = 'Profile updated successfully. Please wait..';
        } else {
            $data['status'] = 'failed';
            $data['message'] = 'Failed to update';
        }
        echo json_encode($data);
    }
}