<?php
namespace Flycart\Pagination;

class Pagination
{
    protected $_start;
    protected $_limit;
    protected $_total;

    public function __construct($start = 0, $limit = 20, $total = null)
    {
        $this->_start = $start;
        $this->_limit = $limit;
        $this->_total = $total;
    }

    public function getLimitBox(){
        $limitStart = 5;
        $limitHtml = '<select name="limit" id="limit">';
        for($i = 1; $i <= 20; $i++) {
            $val = $i*$limitStart;
            if($val == $this->_limit)
                $selected = 'selected';
            else
                $selected = '';
            $limitHtml .= '<option value="'.$val.'" '.$selected.'></option>';

        }
        $limitHtml .= '</select>';

        return $limitHtml;
    }
}