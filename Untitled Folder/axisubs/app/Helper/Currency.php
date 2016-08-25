<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

use Axisubs\Models\Config;

class Currency
{
    protected $defaultCurrency = 'USD';
    protected $defaultCurrencyCode = '$';

    public function __construct()
    {
        $config = Config::all();
        if(!empty($config)){
            if(isset($config->meta[$config->ID.'_axisubs_config_currency_code']) && $config->meta[$config->ID.'_axisubs_config_currency_code'] != ''){
                $this->defaultCurrencyCode = $config->meta[$config->ID.'_axisubs_config_currency_code'];
            }
            if(isset($config->meta[$config->ID.'_axisubs_config_currency']) && $config->meta[$config->ID.'_axisubs_config_currency'] != ''){
                $this->defaultCurrency = $config->meta[$config->ID.'_axisubs_config_currency'];
            }
        }
    }

    //Add currency code
    public function addCurrencyCode($amount){
        return $this->defaultCurrencyCode.' '.$amount;
    }

    public function getCurrencyCode(){
        return $this->defaultCurrencyCode;
    }

    public function getCurrency(){
        return $this->defaultCurrency;
    }
}