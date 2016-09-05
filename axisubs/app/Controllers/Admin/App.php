<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Models\Admin\App as ModelApp;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Controllers\Controller;

class App extends Controller{
    
    public $_controller = 'App';

    /**
     * Default page
     * */
    public function index()
    {
        $pagetitle = "Apps";
        $apps = ModelApp::getAllApps();
        return view('@Axisubs/Admin/app/list.twig', compact('pagetitle','apps'));
    }

    /**
     * Disable an app
     * */
    public function disable()
    {
        $http = Http::capture();
        $result = ModelApp::disableApp($http->get('p'));
        if ( is_wp_error( $result ) ) {
            Notifier::error('Failed to disable');
        } else {
            Notifier::success('Disabled successfully');
        }

        return $this->index();
    }

    /**
     * Enable an app
     * */
    public function enable()
    {
        $http = Http::capture();
        $result = ModelApp::enableApp($http->get('p'));
        if ( is_wp_error( $result ) ) {
            Notifier::error('Failed to enable');
        } else {
            Notifier::success('Enabled successfully');
        }

        return $this->index();
    }

    /**
     * View an app
     * */
    public function view()
    {
        $http = Http::capture();
        $result = ModelApp::loadAppView($http->get('p'));
        return ;
    }

    /**
     * App task
     * */
    public function appTask()
    {
        $http = Http::capture();
        $result = ModelApp::loadAppTask($http->get('p'));
        return ;
    }
}