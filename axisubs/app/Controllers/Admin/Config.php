<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Models\Admin\Config as ModelConfig;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

class Config{
    public function index(Http $http){
        if ($http->has('task')) {
            if($http->get('task') == 'save'){
                $axisubPost = $http->get('axisubs');
                if(isset($axisubPost['config'])) {
                    $result = ModelConfig::saveConfig($http->all());
                    if($result){
                        Notifier::success('Saved successfully');
                    } else {
                        Notifier::error('Failed to save');
                    }
                }
            }
        }
        $all = ModelConfig::all();
        $pagetitle = "Configuration";
        return view('@Axisubs/Admin/config/edit.twig', compact('all','pagetitle'));
    }
}