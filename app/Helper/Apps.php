<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

use Axisubs\Models\Admin\App;
use Events\Event;
use Herbert\Framework\Http;

class Apps
{
    /**
     * Get active payment apps
     * */
    public function getActiveApps(){
        $apps = App::getActiveApps();
        return $apps;
    }

    /**
     * Get html contents
     * */
    public function loadHtml($funtionName, $params = array()){
        $apps = $this->getActiveApps();
        $html = '';
        if(count($apps)){
            $priority = array();
            foreach ($apps as $key => $value){
                $available = Event::trigger( $value['pluginFolder'].'_hasFunction', $funtionName, 'filter');
                if($available){
                    $priority[$value['pluginFolder']] = $available;
                }
            }
            if(count($priority)){
                arsort($priority);// For descending based on value
                foreach ($priority as $key => $value){
                    $content = Event::trigger( $key.'_'.$funtionName, $params, 'filter');
                    if(!is_array($content)){
                        $html .= $content;
                    }
                }
            }
        }
        return $html;
    }
}