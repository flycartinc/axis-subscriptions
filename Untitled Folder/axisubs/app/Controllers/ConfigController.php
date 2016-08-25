<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers;

use Axisubs\Models\Config;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

class ConfigController{
    public function index(Http $http){
        if ($http->has('task')) {
            if($http->get('task') == 'save'){
                $axisubPost = $http->get('axisubs');
                if(isset($axisubPost['config'])) {
                    $result = Config::saveConfig($http->all());
                    if($result){
                        Notifier::success('Saved successfully');
                    } else {
                        Notifier::error('Failed to save');
                    }
                }
            }
        }
        $all = Config::all();
        $pagetitle = "Configuration";
        return view('@Axisubs/Admin/config/edit.twig', compact('all','pagetitle'));
    }
}