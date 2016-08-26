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
        $limitHtml = '<select name="limit" id="limit" onchange="document.getElementById(\'limitstart\').value=0;document.getElementById(\'axisubs_list_form\').submit();">';
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

    public function getPaginationLinks($limitPage = 2){
        $html = '';
        if($this->_total > $this->_limit){
            $rem = $this->_total % $this->_limit;
            $totalPage = floor($this->_total / $this->_limit);
            if($rem>0){
                $totalPage++;
            }
            $html .= '<ul class="pagination-ul">';
            if($this->_start == 0) {
                $html .= '<li class="pag-disabled"><</li>';
            } else {
                $html .= '<li><a href="#" ';
                $html .= 'onclick="document.getElementById(\'limitstart\').value=0;document.getElementById(\'axisubs_list_form\').submit();"';
                $html .= '><</a></li>';
            }
            $i = 1;
//            if($limitPage){
//                if($totalPage-$limitPage){
//                    $i = $limitPage-1;
//                    $html .= '<li class="pag-disabled">';
//                    $html .= '...';
//                    $html .='</li>';
//                } else {
//                    $i = 1;
//                }
//            } else {
//                $i = 1;
//            }
            $limitPageCount = 0;
            for ($i; $i <= $totalPage; $i++){
                $start = ($i-1);
//                if($limitPage && (($limitPageCount) == $limitPage+1)){
//                    $html .= '<li class="pag-disabled">';
//                    $html .= '...';
//                    $html .='</li>';
//                    break;
//                }
                if($start != $this->_start) {
                    $html .= '<li>';
                    $html .= '<a href="#" ';
                    $html .= 'onclick="document.getElementById(\'limitstart\').value=' . $start . ';document.getElementById(\'axisubs_list_form\').submit();"';
                    $html .= '>' . $i . '</a>';
                } else {
                    $html .= '<li class="pag-disabled">';
                    $html .= $i;
                }
                $html .='</li>';
                $limitPageCount++;
            }
            if($this->_start == ($totalPage-1)) {
                $html .= '<li class="pag-disabled">'.'>'.'</li>';
            } else {
                $html .= '<li><a href="#" ';
                $html .= 'onclick="document.getElementById(\'limitstart\').value='.($totalPage-1).';document.getElementById(\'axisubs_list_form\').submit();"';
                $html .= '>></a></li>';
            }
            $html .= '</ul>';
            $html .= '<input type="hidden" id="limitstart" name="limitstart" value="'.$this->_start.'">';
        }
        return $html;
    }
}