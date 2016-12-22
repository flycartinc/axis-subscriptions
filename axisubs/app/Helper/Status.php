<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 16/7/16
 * Time: 3:44 PM
 */

namespace Axisubs\Helper;

class Status
{
    /**
     * get status text
     * */
    public function getStatusText($code){
        return $this->getTextFromCode($code);
    }

    /**
     * get status text in HTML
     * */
    public function getStatusTextInHTML($code){
        $status = Status::getAllStatusCodesWithHtml();
        if(isset($status[$code])){
            return $status[$code];
        } else {
            return $code;
        }
    }

    /**
     * Status codes
     * */
    public static function getAllStatusCodes(){
        $status = array('ORDER_PAGE' => 'New',
            'ACTIVE' => 'Active',
            'PENDING' => 'Pending',
            'TRIAL' => 'In Trial',
            'EXPIRED' => 'Expired',
            'CANCELED' => 'Canceled',
            'FUTURE' => 'Future');
        return $status;
    }

    /**
     * Status codes with html
     * */
    public static function getAllStatusCodesWithHtml(){
        $status = array('ORDER_PAGE' => '<span class="label new">New</span>',
            'ACTIVE' => '<span class="label active">Active</span>',
            'PENDING' => '<span class="label pending">Pending</span>',
            'TRIAL' => '<span class="label in-trial">In Trial</span>',
            'EXPIRED' => '<span class="label expired">Expired</span>',
            'CANCELED' => '<span class="label canceled">Canceled</span>',
            'FUTURE' => '<span class="label future">Future</span>');
        return $status;
    }

    protected function getTextFromCode($code){
        $text = '';
        switch ($code){
            case 'ORDER_PAGE':
                $text = 'New';
                break;
            case 'ACTIVE':
                $text = 'Active';
                break;
            case 'PENDING':
                $text = 'Pending';
                break;
            case 'TRIAL':
                $text = 'In Trial';
                break;
            case 'EXPIRED':
                $text = 'Expired';
                break;
            case 'CANCELED':
                $text = 'Canceled';
                break;
            case 'FUTURE':
                $text = 'Future';
                break;
            default:
                $text = $code;
        }
        return $text;
    }
}