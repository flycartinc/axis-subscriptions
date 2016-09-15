<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Site;

use Axisubs\Controllers\Controller;
use Axisubs\Models\Site\Plans;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper;
use Axisubs\Models\Site\User as ModelUser;

class User extends Controller
{
    /**
     * For user Login through ajax
     * */
    public function loginUser()
    {
        $http = Http::capture();
        $task = $http->get('task');
        if ($task == 'loginUser') {
            $result = ModelUser::loginUser($http->all());
        } else {
            $result['status'] = 'failed';
            $result['message'] = 'Something goes wrong.';
        }
        echo json_encode($result);

    }

    /**
     * For register an user through ajax
     * */
    public function registerUser(){
        $http = Http::capture();
        $result = ModelUser::registerUser($http->all());
        echo json_encode($result);
    }

    /**
     * For update user profile through ajax
     * */
    public function updateProfile(){
        $http = Http::capture();
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

    /**
     * For load provinces through ajax
     * */
    public function loadProvinces(){
        $modelZone = $this->getModel('Zones');
        $http = Http::capture();
        if($http->get('code')){
            echo $modelZone->getProvinceSelectBoxOptions($http->get('code'));
        } else {
            echo '<option value="">-- Select Province --</option>';
        }
    }
}