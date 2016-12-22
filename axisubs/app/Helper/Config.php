<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

use Axisubs\Models\Admin\Config as Configuration;

class Config
{
    public static $instance = null;

    public $config;

    /**
     * get an instance
     * @param array $config
     * @return
     * * */
    public static function getInstance(array $config = array())
    {
        if (!self::$instance)
        {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    public function __construct()
    {
        $config = Configuration::all();
        $this->config = $config;
    }

    //get Data
    public function get($fieldName, $default = ''){
        $result = $default;
        if($this->config){
            $meta = $this->config->meta;
            $configPrefix = $this->config->ID.'_'.$this->config->post_type.'_';
            if(isset($meta[$configPrefix.$fieldName]) && $meta[$configPrefix.$fieldName] != ''){
                $result = $meta[$configPrefix.$fieldName];
            }
        }

        return $result;
    }
}