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
    public function getStatusText($code){
        return $this->getTextFromCode($code);
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
            default:
                $text = $code;
        }
        return $text;
    }
}