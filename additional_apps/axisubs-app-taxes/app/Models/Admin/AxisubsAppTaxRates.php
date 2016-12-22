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
use Axisubs\Helper\AxisubsRedirect;
use Illuminate\Database\Eloquent\Model;

class AxisubsAppTaxRates extends Model
{
    public $_folder = 'axisubs-app-taxes';

    protected $table = 'axisubs_taxrates';
    protected $primaryKey = 'axisubs_taxrate_id';
    /**
     * The table associated with the model.
     *
     * @var string
     */

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


    public static function populateStates($post){
        if(isset($post['limitstart']) && $post['limitstart']){
            AxisubsAppTaxRates::$_start = $post['limitstart'];
        } else {
            AxisubsAppTaxRates::$_start = 0;
        }
        if(isset($post['limit']) && $post['limit']){
            AxisubsAppTaxRates::$_limit = $post['limit'];
        } else {
            AxisubsAppTaxRates::$_limit = 10;
        }
    }

    public static function getPaginationStartAndLimit($total = 0){
        AxisubsAppTaxRates::$_total = $total;
        $balance = AxisubsAppTaxRates::$_total-(AxisubsAppTaxRates::$_limit*AxisubsAppTaxRates::$_start);
        if($balance < AxisubsAppTaxRates::$_limit){
            $limit = $balance;
        } else {
            $limit = AxisubsAppTaxRates::$_limit;
        }
        $result['start'] = AxisubsAppTaxRates::$_start;
        $result['limit'] = $limit;

        return $result;
    }

    /**
     * Get Taxes
     * */
    public function getTaxes(){
        $totalItem = $this->all();
        //get pagination start and limit
        $pageLimit = AxisubsAppTaxRates::getPaginationStartAndLimit(count($totalItem));
        //get limited data
        $items = $totalItem->forPage($pageLimit['start'], $pageLimit['limit']);
        return $items;
    }

    /**
     * Get single Tax
     * */
    public function getTax($id){
        $item = $this->all()->where('axisubs_taxrate_id', (int)$id)->first();
        return $item;
    }

    /**
     * Save a tax
     * */
    public function saveTax($post){
        if(isset($post['id']) && $post['id']){
            $model = $this->all()->where('axisubs_taxrate_id', (int)$post['id'])->first();
        } else {
            $model = $this->newInstance();
        }

        $model->tax_rate_class = "standard";
        foreach ($post['axisubs']['tax'] as $field => $value){
            $model->$field = $value;
        }

        $result = $model->save();
        if($result){
            return $model->axisubs_taxrate_id;
        } else {
            return false;
        }
    }

    /**
     * Delete Tax
     * */
    public function deleteTax($id){
        if($id){
            $model = $this->all()->where('axisubs_taxrate_id', (int)$id)->first();
            if(!empty($model)){
                return $model->delete();
            } else {
                AxisubsRedirect::redirect('?page=app-index&task=view&p=axisubs-app-taxes');
            }
        } else {
            return false;
        }
    }
}