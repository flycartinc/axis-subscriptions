<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers;

use Axisubs\Models\App;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

class AppController{
    public function index(Http $http){
        if ($http->has('task')) {
            $task = $http->get('task');
            switch ($task){
                case 'disable':
                    $result = App::disableApp($http->get('p'));
                    if ( is_wp_error( $result ) ) {
                        Notifier::error('Failed to disable');
                    } else {
                        Notifier::success('Disabled successfully');
                    }
                break;
                case 'enable':
                    $result = App::enableApp($http->get('p'));
                    if ( is_wp_error( $result ) ) {
                        Notifier::error('Failed to enable');
                    } else {
                        Notifier::success('Enabled successfully');
                    }
                break;
                case 'view':
                    $result = App::loadAppView($http->get('p'));
                    return;
                break;
            }
        }
        /*$all = Config::all();*/
        $pagetitle = "Apps";
        //$content = do_action('loadPluginNameFunction');
        $apps = App::getAllApps();
//        $output = '';
//        $content[]   = apply_filters( 'loadPluginNameFunction', $output);
//       print_r($content);exit;
        // echo $output;
       // echo $content;
        return view('@Axisubs/Admin/app/list.twig', compact('pagetitle','apps'));
    }

    public function app(Http $http){

    }
}