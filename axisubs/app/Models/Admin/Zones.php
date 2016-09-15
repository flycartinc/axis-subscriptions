<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace Axisubs\Models\Admin;

use Events\Event;
use Illuminate\Database\Eloquent\Model;

class Zones extends Model{

    protected $table = 'axisubs_zones';
    protected $primaryKey = 'axisubs_zone_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get all Zones based on country code
     * */
    public function getZones($countryCode = ''){
        if($countryCode == ''){
            return array();
        } else {
            return $this->all()->where('country_code', $countryCode);
        }
    }

    /**
     * Get Province name based on code
     * */
    public function getProvinceName($provinceCode = '', $countryCode = ''){
        if($provinceCode == ''){
            return '';
        } else {
            $data = $this->all()->where('zone_code', $provinceCode)->where('country_code', $countryCode)->first();
            if(isset($data->zone_name))
                return $data->zone_name;
            else
                return '';
        }
    }

    /**
     * Get Province name based on code
     * */
    public function getProvinceSelectBox($countryCode = '', $selected = '', $name = 'province', $id = 'province', $class = '', $option = 0)
    {
        $provinces =  $this->getZones($countryCode);
        if(!$option)
            $select = '<select name="'.$name.'" id="'.$id.'" class="axisubs_province '.$class.'">';
        else
            $select = '';
        $select .= '<option value="">-- Select Province --</option>';
        foreach ($provinces as $key => $province){
            if($selected == $province->zone_code){
                $selectedText = ' selected="selected"';
            } else {
                $selectedText = '';
            }
            $select .= '<option value="'.$province->zone_code.'"'.$selectedText.'>'.$province->zone_name.'</option>';
        }
        if(!$option)
            $select .= '</select>';

        return $select;
    }

    /**
     * Get Province name based on code for ajax
     * */
    public function getProvinceSelectBoxOptions($countryCode, $selected = '', $name = 'province', $id = 'province', $class = '')
    {
        return $this->getProvinceSelectBox($countryCode, $selected, $name, $id, $class, 1);
    }
}