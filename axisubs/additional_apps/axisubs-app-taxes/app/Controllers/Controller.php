<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace AxisubsAppTaxes\Controllers;

use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

class Controller
{
    public $_controller = '';
    public $_path = 'Admin';
    public $_package = 'AxisubsAppTaxes';

    /**
     * For ajax Call
     * */
    public function ajaxCall(Http $http)
    {
        $task = $http->get('task');
        $controller = $http->get('view');
        $path = $http->get('path', 'Admin');
        $className = '\\'.$this->_package.'\\Controllers\\'.$path.'\\'.$controller;
        if(class_exists($className)){
            $object = new $className();
            if(method_exists($object, $task)){
                return $object->$task();
            } else {
                echo 'function not available'; // TODO: handle error
            }
        } else {
            echo 'Class not available'; // TODO: handle error
        }
    }

    /**
     * For front end ajax call
     * */
    public function ajaxCallSite(Http $http){
        $http->request->set('path', 'Site');
        return $this->ajaxCall($http);
    }
    
    /**
     * Execute
     * */
    public function execute(Http $http){
        $task = $http->get('apptask');
        $controller = $http->get('controller', $this->_controller);
        $path = $http->get('path', $this->_path);
        $className = '\\'.$this->_package.'\\Controllers\\'.$path.'\\'.$controller;
        if(class_exists($className)){
            $object = new $className();
            if(method_exists($object, $task)){
                return $object->$task();
            } else {
                return $object->index(); // Load default page
            }
        } else {
            echo 'Class not available'; // TODO: handle error
        }
    }

    /**
     * Get instance of model
     * */
    public function getModel($model = '', $path = ''){
        if($model == ''){
            $model = $this->_controller;
        }
        if($path == ''){
            $path = $this->_path;
        }
        $className = '\\'.$this->_package.'\\Models\\'.$path.'\\'.$model;
        if(class_exists($className)){
            $object = new $className();
            return $object;
        } else {
            echo 'Class not available'; // TODO: handle error
        }
    }

}