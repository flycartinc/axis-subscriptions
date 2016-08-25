<?php
namespace Axisubs\Helper;

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

    /**
     * For Limit box
     * */
    public function getLimitBox(){
        $limitStart = 5;
        $limitHtml = '<select name="limit" id="limit" onchange="document.getElementById(\'axisubs_list_form\').submit();">';
        for($i = 1; $i <= 20; $i++) {
            $val = $i*$limitStart;
            if($val == $this->_limit)
                $selected = 'selected';
            else
                $selected = '';
            $limitHtml .= '<option value="'.$val.'" '.$selected.'>'.$val.'</option>';
        }
        $limitHtml .= '</select>';

        return $limitHtml;
    }

    public function getPaginationLinks(){
        $limitStart = 5;
        $html = '';
        if($this->_total > $this->_limit){
            $rem = $this->_total % $this->_limit;
            $totalPage = $this->_total / $this->_limit;
            if($rem>0){
                $totalPage++;
            }
            $html .= '<ul class="pagination-ul">';
            $html .= '<li><a href="#"><</a></li>';
            for ($i = 1; $i <= $totalPage; $i++){
                $start = ($i-1);
                $html .= '<li>';
                if($start != $this->_start) {
                    $html .= '<a href="#" ';
                    $html .= 'onclick="document.getElementById(\'limitstart\').value=' . $start . ';document.getElementById(\'axisubs_list_form\').submit();"';
                    $html .= '>' . $i . '</a>';
                } else {
                    $html .= $i;
                }
                $html .='</li>';
            }
            $html .= '<li><a href="#">'.'>'.'</a></li>';
            $html .= '</ul>';
            $html .= '<input type="hidden" id="limitstart" name="limitstart" value="'.$this->_start.'">';
        }
        return $html;
    }
}