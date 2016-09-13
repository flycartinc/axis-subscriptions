<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

class Common
{
    /**
     * For generating invoice number
     * */
    public function getInvoiceNumber($subscription_id){

        return 'axisubs_wp-'.$subscription_id; //TODO: Make it dynamic.
    }
}