<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace Axisubs\Models\Admin;

use Events\Event;
use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
class App extends Post{
    /**
     * The table associated with the model.
     *
     * @var string
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    // Get all App related to Axisubs
    public static function getAllApps(){
        //$plugins = get_option('active_plugins');
        $plugins = get_plugins();
        $axisubsApps = array();
        foreach($plugins as $key => $value){
            $arrayKey = explode('-app-', $key);
            if($arrayKey[0] == 'axisubs'){
                $names = explode('/', $key);
                $axisubsAppsNames = $names[0];
                $value['pluginPath'] = $key;
                $value['pluginFolder'] = $axisubsAppsNames;
                if(is_plugin_active($key)){
                    $value['active'] = true;
                } else {
                    $value['active'] = false;
                }
                $axisubsApps[] = $value;
            }
        }
       // echo "<pre>";print_r($axisubsApps);exit;
        return $axisubsApps;
    }

    /**
     * Get active payment Apps
     * */
    public static function getActivePaymentApps($active = 1){
        $allApps = App::getAllApps();
        $axisubsApps = array();
        foreach($allApps as $key => $value){
            $folderArray = explode('-', $value['pluginFolder']);
            if($active) {
                if ($folderArray[2] == 'payment' && $value['active'] == '1') {
                    $axisubsApps[] = $value;
                }
            } else {
                if ($folderArray[2] == 'payment') {
                    $axisubsApps[] = $value;
                }
            }
        }
        return $axisubsApps;
    }

    /**
     * Get active payment Apps
     * */
    public static function getActiveApps(){
        $allApps = App::getAllApps();
        $axisubsApps = array();
        foreach($allApps as $key => $value){
            if($value['active'] == '1'){
                $axisubsApps[] = $value;
            }
        }
        return $axisubsApps;
    }

    //For enable a plugin
    public static function enableApp($path){
        $result = activate_plugin( $path );
        return $result;
    }

    //for disable aplugin
    public static function disableApp($path){
        $result = deactivate_plugins( $path );
        return $result;
    }

    // For load app View
    public static function loadAppView($folder){
        Event::trigger( $folder.'_loadView', '');
    }

    // For load app View
    public static function loadAppTask($folder){
        Event::trigger( $folder.'_appTask', '');
    }
}