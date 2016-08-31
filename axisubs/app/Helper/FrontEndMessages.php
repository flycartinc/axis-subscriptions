<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

class FrontEndMessages
{
    public static function success($message){
        $html = '<div class="notice-success"><p>'.$message.'</p></div>';
        return $html;
    }

    public static function failure($message){
        $html = '<div class="notice-error"><p>'.$message.'</p></div>';
        return $html;
    }

}