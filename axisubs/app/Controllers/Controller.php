<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers;

use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

class Controller
{
    //For ajax Call
    public function ajaxCall(Http $http)
    {
        $task = $http->get('task');
        $controller = $http->get('view');
        $path = $http->get('path', 'Admin');
        $className = '\\Axisubs\\Controllers\\'.$path.'\\'.$controller;
        if(class_exists($className)){
            $object = new $className();
            if(method_exists($object, $task)){
                return $object->$task();
            } else {
                echo 'function not available';
            }
        } else {
            echo 'Class not available';
        }
    }
    
    public function ajaxCallSite(Http $http){
        $http->request->set('path', 'Site');
        return $this->ajaxCall($http);
    }

    public function execute(Http $http){
        $task = $http->get('task');
        $controller = $http->get('controller');
        $path = $http->get('path', 'Admin');
        $className = '\\Axisubs\\Controllers\\'.$path.'\\'.$controller;
        if(class_exists($className)){
            $object = new $className();
            if(method_exists($object, $task)){
                return $object->$task();
            } else {
                echo 'function not available';
            }
        } else {
            echo 'Class not available';
        }
    }

}