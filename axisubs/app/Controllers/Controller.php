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
    public $_controller = '';
    public $_path = 'Admin';
    public $_package = 'Axisubs';

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
        $task = $http->get('task')? $http->get('task') : get_query_var('task');
        $http->request->set('task', $task);
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

    /**
     * For get the query strings if sef URL
     * */
    public function getQueryStringData($http){
        $vars = array('slug', 'id', 'task', 'sid');
        foreach ($vars as $val){
            if($http->get($val) == null){
                if(get_query_var($val) != null){
                    $http->request->set($val, get_query_var($val));
                }
            }
        }
        return $http;
    }

    /**
     * For get / generate dynamic url
     * */
    public function getAxiSubsURLs($view, $task = '', $id = '', $slug = ''){
        $customPermalink = get_option('permalink_structure');
        $permalinkPlain = $customPermalink ? 0: 1;
        $url = get_site_url();
        if($permalinkPlain){
            if($view == 'plan'){
                $url .= '/index.php?axisubs_plan=plans';
                if($task)
                    $url .= '&task='.$task;
                if($slug)
                    $url .= '&slug='.$slug;
                if($id)
                    $url .= '&id='.$id;
            } else if($view == 'subscribe'){
                $url .= '/index.php?axisubs_subscribes=subscribe';
                if($task)
                    $url .= '&task='.$task;
                if($id)
                    $url .= '&sid='.$id;
            }
        } else {
            $prefix = '';
            $permalinkArray = explode('/index.php', $customPermalink);
            if(isset($permalinkArray[0]) && $permalinkArray[0] == ''){
                $prefix = '/index.php';
            }
            if($view == 'plan'){
                $url .= $prefix.'/axisplan/plans';
                if($task)
                    $url .= '/'.$task;
                if($slug)
                    $url .= '/'.$slug;
                if($id)
                    $url .= '/'.$id;
            } else if($view == 'subscribe'){
                $url .= $prefix.'/axisubs/subscribe';
                if($task)
                    $url .= '/'.$task;
                if($id)
                    $url .= '/'.$id;
            }
        }
        return $url;
    }

}