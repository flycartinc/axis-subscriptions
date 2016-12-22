<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace AxisubsAppTaxes\Models\Admin;

use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
use Herbert\Framework\Http;
use Axisubs\Models\Site\Plans;
use Axisubs\Helper\AxisubsRedirect;
use Axisubs\Helper\DateFormat;

class AxisubsAppTaxes extends Post
{
    public $_folder = 'axisubs-app-taxes';
    /**
     * The table associated with the model.
     *
     * @var string
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $_item = array();

    public static $post_type = 'axisubs_tax';

    public static $post_type_items = 'axisubs_taxes';

    public static $post_type_config = 'axisubs_tax_config';

    public static $_total;
    public static $_start;
    public static $_limit;

    public function __construct(array $attributes = [])
    {
    }

    public static function populateStates($post){
        if(isset($post['limitstart']) && $post['limitstart']){
            AxisubsAppTaxes::$_start = $post['limitstart'];
        } else {
            AxisubsAppTaxes::$_start = 0;
        }
        if(isset($post['limit']) && $post['limit']){
            AxisubsAppTaxes::$_limit = $post['limit'];
        } else {
            AxisubsAppTaxes::$_limit = 10;
        }
    }

    public static function getPaginationStartAndLimit($total = 0){
        AxisubsAppTaxes::$_total = $total;
        $balance = AxisubsAppTaxes::$_total-(Plans::$_limit*Plans::$_start);
        if($balance < AxisubsAppTaxes::$_limit){
            $limit = $balance;
        } else {
            $limit = AxisubsAppTaxes::$_limit;
        }
        $result['start'] = AxisubsAppTaxes::$_start;
        $result['limit'] = $limit;

        return $result;
    }

    /**
     * Get Taxes
     * */
    public function getTaxes(){
        $postO = new Post();
        $totalItem = $postO->where('post_type', AxisubsAppTaxes::$post_type_items)->get();
        //get pagination start and limit
        $pageLimit = AxisubsAppTaxes::getPaginationStartAndLimit(count($totalItem));
        //get limited data
        $items = $totalItem->forPage($pageLimit['start'], $pageLimit['limit']);
        if(count($items)){
            foreach ($items as $key => $item) {
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            }
        }
        return $items;
    }

    /**
     * Get single Tax
     * */
    public function getTax($id){
        $item = Post::where('post_type', AxisubsAppTaxes::$post_type_items)->find($id);
        if($item) {
            $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
        }
        return $item;
    }

    /**
     * Get Tax Config
     * */
    public function getConfig(){
        $item = parent::all()->where('post_type', AxisubsAppTaxes::$post_type_config)->first();
        if(!empty($item)) {
            if ($item->meta() != null) {
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            }
        }
        return $item;
    }

    /**
     * Save a tax
     * */
    public static function saveTax($post){
        if(isset($post['id']) && $post['id']){
            $postDB = Post::where('post_type', AxisubsAppTaxes::$post_type_items)->get();
            $postTable = $postDB->find($post['id']);
        } else {
            $postTable = new Post();
            $postTable->post_name = 'Axisubs Taxes';
            $postTable->post_title = 'Axisubs Taxes';
            $postTable->post_type = AxisubsAppTaxes::$post_type_items;
            $postTable->save();
        }

        foreach ($post['axisubs']['tax'] as $key => $val) {
            $key = $postTable->ID . '_'.$postTable->post_type.'_' . $key;
            if(is_array($val)){
                $postTable->meta->$key = implode(',', $val);
            } else {
                $postTable->meta->$key = $val;
            }
        }
        $result = $postTable->save();
        if($result){
            return $postTable->ID;
        } else {
            return false;
        }
    }

    /**
     * Save a tax
     * */
    public static function saveTaxConfig($post){
        if(isset($post['id']) && $post['id']){
            $postDB = Post::where('post_type', AxisubsAppTaxes::$post_type_config)->get();
            $postTable = $postDB->find($post['id']);
        } else {
            $postTable = new Post();
            $postTable->post_name = 'Axisubs Tax Config';
            $postTable->post_title = 'Axisubs Tax Config';
            $postTable->post_type = AxisubsAppTaxes::$post_type_config;
            $postTable->save();
        }

        foreach ($post['axisubs']['config'] as $key => $val) {
            $key = $postTable->ID . '_'.$postTable->post_type.'_' . $key;
            if(is_array($val)){
                $postTable->meta->$key = implode(',', $val);
            } else {
                $postTable->meta->$key = $val;
            }
        }
        $result = $postTable->save();
        if($result){
            return $postTable->ID;
        } else {
            return false;
        }
    }

    /**
     * Delete Tax
     * */
    public static function deleteTax($id){
        if($id){
            $postDB = Post::where('post_type', AxisubsAppTaxes::$post_type_items)->get();
            $postTable = $postDB->find($id);
            if(!empty($postTable)){
                $postTable->meta()->delete();
                return $postTable->delete();
            } else {
                AxisubsRedirect::redirect('?page=app-index&task=view&p=axisubs-app-taxes');
            }
        } else {
            return false;
        }
    }

    /**
     * to get EU Countries
     * */
    public function getEUCountries() {

        return array(
            'AT' => 'AT', //Austria
            'BE' => 'BE', //Belgium
            'BG' => 'BG', //Bulgaria
            'CY' => 'CY', //Cyprus
            'CZ' => 'CZ', //Czech Republic
            'HR' => 'HR', //Croatia
            'DK' => 'DK', //Denmark
            'EE' => 'EE', //Estonia
            'FI' => 'FI', //Finland
            'FR' => 'FR', //France
            'FX' => 'FR', //France métropolitaine
            'DE' => 'DE', //Germany
            'GR' => 'EL', //Greece
            'HU' => 'HU', //Hungary
            'IE' => 'IE', //Irland
            'IT' => 'IT', //Italy
            'LV' => 'LV', //Latvia
            'LT' => 'LT', //Lithuania
            'LU' => 'LU', //Luxembourg
            'MT' => 'MT', //Malta
            'NL' => 'NL', //Netherlands
            'PL' => 'PL', //Poland
            'PT' => 'PT', //Portugal
            'RO' => 'RO', //Romania
            'SK' => 'SK', //Slovakia
            'SI' => 'SI',  //Slovania
            'ES' => 'ES', //Spain
            'SE' => 'SE', //Sweden
            'GB' => 'GB' //United Kingdom
        );
    }

    /**
     * Validate Vat Number
     * */
    public function validateVatNumber($country_code, $number){
        $org_number = $number;
        $number = str_replace($country_code, "", $org_number);
        $status = 0;
        if(!class_exists('SoapClient')) {
            require_once(AXISUBS_APP_TAXES_PLUGIN_PATH.'app/library/class.euvat.php');
            $vatValidation = new vatValidation( array('debug' => false));
            if($vatValidation->check($country_code, $number)) {
                $status = 1;
            } else {
                $status = 0;
            }
        } else {
            $response = file_get_contents('http://ec.europa.eu/taxation_customs/vies/viesquer.do?ms=' . $country_code . '&iso=' .$country_code. '&vat=' . $number);
            if (preg_match('/\bvalid VAT number\b/i', $response)) {
                $status = 1;
            }

            if (preg_match('/\binvalid VAT number\b/i', $response)) {
                $status = 0;
            }
        }

        return $status;
    }
}