<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace AxisubsAppTaxes\Controllers\Admin;
use AxisubsAppTaxes\Controllers\Controller;
use AxisubsAppTaxes\Models\Admin\AxisubsAppTaxes as AxisubsAppTaxesModel;
use AxisubsAppTaxes\Models\Admin\AxisubsAppTaxRates;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper\Common;
use Axisubs\Models\Admin\Config;
use Axisubs\Helper\Pagination;
use Axisubs\Helper\Currency;
use Axisubs\Helper\Countries;
use AxisubsAppTaxes\Helper\Tax;
use Axisubs\Helper\Config as HelperConfig;

class AxisubsAppTaxes extends Controller
{
    public $_controller = 'AxisubsAppTaxes';
    public $_element = 'app_taxes';
    public $_folder = 'axisubs-app-taxes';

    /**
     * Default App view
     * */
    public static function loadAppView(){
        $currentObject = new AxisubsAppTaxes();
        $currentObject->index();
    }

    /**
     * Load tax details in plan detail page
     * */
    public static function loadTaxDetails($plan, $subscriptions, $page){
        if(isset($plan['additionalPrice']['tax_total']) && (int)$plan['additionalPrice']['tax_total'] > 0){
            $tax_details = $plan['additionalPrice']['tax_details'];
            if($tax_details != ''){
                $tax_details = (array)json_decode($tax_details);
                $currency = new Currency();
                $currencyData['code'] = $currency->getCurrencyCode();
                $currencyData['currency'] = $currency->getCurrency();
                $data['page'] = $page;
                $data['currencyData'] = $currencyData;
                $dataHtml = view('@AxisubsAppTaxes/Site/List/list.twig', compact('tax_details', 'data', 'plan'));
                if($dataHtml->getStatusCode() == '200'){
                    return $dataHtml->getBody();
                } else {
                    // echo "Something goes wrong";
                }
            }
        } else {

        }

    }

    /**
     * Default layout
     * */
    public function index()
    {
        $pagetitle = "Taxes";
        $model = $this->getModel('AxisubsAppTaxRates');
        $http = Http::capture();
        AxisubsAppTaxRates::populateStates($http->all());
        $items = $model->getTaxes();
        $pagination = new Pagination(AxisubsAppTaxRates::$_start, AxisubsAppTaxRates::$_limit, AxisubsAppTaxRates::$_total);
        $paginationD['limitbox'] = $pagination->getLimitBox();
        $paginationD['links'] = $pagination->getPaginationLinks();
        $data['countries'] = Countries::getCountries();
        if(count($items)){
            $this->_package = 'Axisubs';
            $modelZone = $this->getModel('Zones');
            foreach ($items as $key => $item) {
                if(isset($item->tax_rate_state) && $item->tax_rate_state != ''){
                    $item->province_name = $modelZone->getProvinceName($item->tax_rate_state, $item->tax_rate_country);
                } else {
                    $item->province_name = '';
                }
            }
            $this->_package = 'AxisubsAppTaxes';
        }

        $htmlView = view('@AxisubsAppTaxes/Admin/List/default.twig', compact('pagetitle', 'items', 'paginationD', 'data'));
        if($htmlView->getStatusCode() == '200'){
            echo $htmlView->getBody();
        } else {
            echo "Something goes wrong";
        }
    }

    public function editConfig(){
        $pagetitle = "Tax Settings";
        $http = Http::capture();
        $model = $this->getModel();
        $item = $model->getConfig();
        $site_url = get_site_url();
        $htmlView = view('@AxisubsAppTaxes/Admin/Form/editconfig.twig', compact('pagetitle', 'item', 'site_url', 'data'));
        if($htmlView->getStatusCode() == '200'){
            echo $htmlView->getBody();
        } else {
            echo "Something goes wrong";
        }
    }

    /**
     * Edit layout
     * */
    public function edit($id = 0){
        $pagetitle = "Add Tax";
        $http = Http::capture();
        $model = $this->getModel('AxisubsAppTaxRates');
        $item = array();
        $taxCountry = $taxProvince = '';
        if ($http->get('id') || $id) {
            if($http->get('id')){
                $id = $http->get('id');
            }
            $item = $model->getTax($id);
            if (!empty($item)) {
                $pagetitle = 'Edit Tax';
                $taxProvince = $item->tax_rate_state;
                $taxCountry = $item->tax_rate_country;
            }
        }
        $this->_package = 'Axisubs';
        $modelZone = $this->getModel('Zones');
        $data['country'] = Countries::getCountriesSelectBox($taxCountry, 'axisubs[tax][tax_rate_country]', 'axisubs_tax_tax_rate_country', 'required');
        $data['province'] = $modelZone->getProvinceSelectBox($taxCountry, $taxProvince, 'axisubs[tax][tax_rate_state]', 'axisubs_tax_tax_rate_state', '');
        $this->_package = 'AxisubsAppTaxes';
        $site_url = get_site_url();
        $data = view('@AxisubsAppTaxes/Admin/Form/edit.twig', compact('pagetitle', 'item', 'site_url', 'data'));
        if($data->getStatusCode() == '200'){
            echo $data->getBody();
        } else {
            echo "Something goes wrong";
        }
    }

    /**
     * Save Tax
     * */
    public function save(){
        $http = Http::capture();
        $axisubPost = $http->get('axisubs');
        $item = array();
        if (isset($axisubPost['tax'])) {
            $model = $this->getModel('AxisubsAppTaxRates');
            $result = $model->saveTax($http->all());
            if ($result) {
                Notifier::success('Saved successfully');
                return $this->edit($result);
            }
        }
        Notifier::error('Failed to save');
        return $this->index();
    }

    /**
     * Save Tax config
     * */
    public function saveConfig(){
        $http = Http::capture();
        $axisubPost = $http->get('axisubs');
        $item = array();
        if (isset($axisubPost['config'])) {
            $result = AxisubsAppTaxesModel::saveTaxConfig($http->all());
            if ($result) {
                Notifier::success('Saved successfully');
                return $this->editConfig();
            }
        }
        Notifier::error('Failed to save');
        return $this->index();
    }

    /**
     * Delete Tax
     * */
    public function delete(){
        $http = Http::capture();
        if ($http->get('id')) {
            $model = $this->getModel('AxisubsAppTaxRates');
            $result = $model->deleteTax($http->get('id'));
            if ($result) {
                Notifier::success('Deleted successfully');
            } else {
                Notifier::error('Failed to delete');
            }
        }
        return $this->index();
    }

    /**
     * Calculate tax
     * */
    public function calculateTaxTotals($item){
        if($item['additionalPrice']){
            $additionalPrice = $item['additionalPrice'];
        } else {
            $additionalPrice = array();
        }
        $tax_class = 'standard';
        $model = $this->getModel();
        $config = $model->getConfig();
        $configPrefix = $config->ID.'_'.$config->post_type.'_';
        $enable_tax = isset($config->meta[$configPrefix.'enable']) ? $config->meta[$configPrefix.'enable']: 0;
        $include_exclude_tax = isset($config->meta[$configPrefix.'tax_type']) ? $config->meta[$configPrefix.'tax_type']: 'excluding_tax';
        $enable_tax_info = isset($config->meta[$configPrefix.'display_tax_info']) ? $config->meta[$configPrefix.'display_tax_info']: 1;
        $line_price = $this->getMetaData($item, 'total_price');
        $line_price_org = $line_price;
        $original_price = $this->getMetaData($item, 'original_price');
        //Check tax is applicable
        if($enable_tax){
            $enable_tax = $this->checkTaxIsApplicable($item, $config);
        }
        // Checking tax is enabled
        if(!$enable_tax){
            $line_subtotal 		= $line_price;
            $line_subtotal_tax  = 0;
            $tax_details = '';
        } else if($include_exclude_tax == 'including_tax'){
            $additionalPrice['tax_type'] = 'including_tax';
            $line_price = $original_price;
            // include tax

            // Get base tax rates
            if ( empty( $shop_tax_rates[ $tax_class ] ) ) {
                $shop_tax_rates[ $tax_class ] = Tax::get_base_tax_rates( $tax_class );
            }

            // Get item tax rates
            if ( empty( $tax_rates[ $tax_class ] ) ) {
                $tax_rates[ $tax_class ] = Tax::get_rates( $tax_class );
            }

            $base_tax_rates = $shop_tax_rates[ $tax_class ];
            $item_tax_rates = $tax_rates[ $tax_class ];

            /**
             * ADJUST TAX - Calculations when base tax is not equal to the item tax.
             *
             * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
             * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
             * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
             */
            if ( $item_tax_rates !== $base_tax_rates ) {

                // Work out a new base price without the shop's base tax
                $taxes                 = Tax::calc_tax( $line_price, $base_tax_rates, true, true );

                // Now we have a new item price (excluding TAX)
                $line_subtotal         = $line_price - array_sum( $taxes );

                // Now add modified taxes
                $tax_result            = Tax::calc_tax( $line_subtotal, $item_tax_rates );

                //To display each tax price in front end
                foreach($tax_result as $k => $tax_rates){
                    $item_tax_rates[$k]['price'] = Common::roundPrice($tax_rates);
                }
                $tax_details = $item_tax_rates;

                $line_subtotal_tax     = array_sum( $tax_result );
                //$line_subtotal         = $line_price - $line_subtotal_tax;
                $line_subtotal         = $line_price_org - $line_subtotal_tax;

                /**
                 * Regular tax calculation (customer inside base and the tax class is unmodified.
                 */
            } else {

                // Calc tax normally
                $taxes                 = Tax::calc_tax( $line_price , $item_tax_rates, true );

                //To display each tax price in front end
                foreach($taxes as $k => $tax_rates){
                    $item_tax_rates[$k]['price'] = Common::roundPrice($tax_rates);
                }
                $tax_details = $item_tax_rates;

                $line_subtotal_tax     = array_sum( $taxes );
                //$line_subtotal         = $line_price - array_sum( $taxes );
                $line_subtotal         = $line_price_org - array_sum( $taxes );
            }
        } else {
            $additionalPrice['tax_type'] = 'excluding_tax';
            $line_price = $original_price;
            // exluding tax
            if ( ! empty( $tax_class ) ) {

            }
            $tax_rates[ $tax_class ]  = Tax::get_rates( $tax_class );
            $item_tax_rates        = $tax_rates[ $tax_class ];

            // Base tax for line before discount - we will store this in the order data
            $taxes                 = Tax::calc_tax( $line_price, $item_tax_rates );

            //To display each tax price in front end
            foreach($taxes as $k => $tax_rates){
                $item_tax_rates[$k]['price'] = Common::roundPrice($tax_rates);
            }
            $tax_details = $item_tax_rates;

            $line_subtotal_tax     = array_sum( $taxes );
            $line_subtotal         = $line_price_org;
        }

        // Add to main subtotal $original_price
        $subtotal        	= Common::roundPrice($line_subtotal) + Common::roundPrice($line_subtotal_tax);
        $subtotal_ex_tax 	= Common::roundPrice($line_subtotal);
        $tax 				= Common::roundPrice($line_subtotal_tax);
        $params = '';
        $params['tax_details'] = $tax_details;
        $additionalPrice['tax_total'] = Common::roundPrice($tax);
        $additionalPrice['total_excluding_tax'] = $subtotal_ex_tax;
        $additionalPrice['tax_details'] = json_encode($tax_details);
        $item['additionalPrice'] = $additionalPrice;
        if($enable_tax && $enable_tax_info){
            $item['tax_info_html'] = $this->getTaxInfoHtml($item);
        }
        $this->setMetaData($item, 'total_price', Common::roundPrice($subtotal));
    }

    /**
     * Calculate tax discount
     * */
    public function calculateTaxDiscounts($item){

        if($item['additionalPrice']){
            $additionalPrice = $item['additionalPrice'];
        } else {
            $additionalPrice = array();
        }
        $tax_class = 'standard';
        $model = $this->getModel();
        $config = $model->getConfig();
        $configPrefix = $config->ID.'_'.$config->post_type.'_';
        $enable_tax = isset($config->meta[$configPrefix.'enable']) ? $config->meta[$configPrefix.'enable']: 0;
        $include_exclude_tax = isset($config->meta[$configPrefix.'tax_type']) ? $config->meta[$configPrefix.'tax_type']: 'excluding_tax';
        $total_product_linePrice = $this->getMetaData($item, 'total_price');
        if(isset($item['additionalPrice']['discount']) && $item['additionalPrice']['discount'] > 0){
            $line_price = $item['additionalPrice']['discount'];
        } else {
            return ;
        }

        //Check tax is applicable
        if($enable_tax){
            $enable_tax = $this->checkTaxIsApplicable($item, $config);
        }

        if(!$enable_tax) {
            // just return an empty amount
            $line_subtotal 		= $line_price;
            $line_subtotal_tax  = 0;
            $subtotal = $total_product_linePrice;

        } else if($include_exclude_tax == 'including_tax'){
            // include tax

            // Get base tax rates
            if ( empty( $shop_tax_rates[ $tax_class ] ) ) {
                $shop_tax_rates[ $tax_class ] = Tax::get_base_tax_rates( $tax_class );
            }

            // Get item tax rates
            if ( empty( $tax_rates[ $tax_class ] ) ) {
                $tax_rates[ $tax_class ] = Tax::get_rates( $tax_class );
            }

            $base_tax_rates = $shop_tax_rates[ $tax_class ];
            $item_tax_rates = $tax_rates[ $tax_class ];

            /**
             * ADJUST TAX - Calculations when base tax is not equal to the item tax.
             *
             * The non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
             * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
             * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
             */
            if ( $item_tax_rates !== $base_tax_rates ) {

                // Work out a new base price without the shop's base tax
                $taxes                 = Tax::calc_tax( $line_price, $base_tax_rates, true, true );

                // Now we have a new item price (excluding TAX)
                $line_subtotal         = $line_price - array_sum( $taxes );

                // Now add modified taxes
                $tax_result            = Tax::calc_tax( $line_subtotal, $item_tax_rates );
                $line_subtotal_tax     = array_sum( $tax_result );
                $subtotal        	= $total_product_linePrice;
                /**
                 * Regular tax calculation (customer inside base and the tax class is unmodified.
                 */
            } else {

                // Calc tax normally
                $taxes                 = Tax::calc_tax( $line_price , $item_tax_rates, true );
                $line_subtotal_tax     = array_sum( $taxes );
                $line_subtotal         = $line_price - array_sum( $taxes );
                $subtotal        	= $total_product_linePrice;
            }

        } else {
            // exluding tax
            if ( ! empty( $tax_class ) ) {

            }
            $tax_rates[ $tax_class ]  = Tax::get_rates( $tax_class );
            $item_tax_rates        = $tax_rates[ $tax_class ];


            // Base tax for line before discount - we will store this in the order data
            $taxes                 = Tax::calc_tax( $line_price, $item_tax_rates );
            $line_subtotal_tax     = array_sum( $taxes );
            $line_subtotal         = $line_price;
            $subtotal        	= $total_product_linePrice + Common::roundPrice($line_subtotal_tax);
        }

        // Add to main subtotal

        $subtotal_ex_tax 	= $line_subtotal;
        $tax_total 			= $additionalPrice['tax_total']+Common::roundPrice($line_subtotal_tax);
        $additionalPrice['tax_total'] = Common::roundPrice($tax_total);
        $additionalPrice['tax_total_ex_discount_tax'] = Common::roundPrice($subtotal_ex_tax);
        $additionalPrice['discount_tax'] = Common::roundPrice($line_subtotal_tax);
        $item['additionalPrice'] = $additionalPrice;
        $this->setMetaData($item, 'total_price', Common::roundPrice($subtotal));
    }

    /**
     * get meta value
     * */
    protected function getMetaData($item, $field){
        if(isset($item->meta)){
            $data = $item->meta[$field];
        } else {
            $data = $item['meta'][$field];;
        }

        return $data;
    }

    /**
     * set meta value
     * */
    protected function setMetaData($item, $field, $value){
        if(isset($item->meta)){
            $metaArray = $item->meta;
            $metaArray[$field] = $value;
            $item->meta = $metaArray;
        } else {
            $metaArray = $item['meta'];
            $metaArray[$field] = $value;
            $item['meta'] = $metaArray;
        }
    }

    /**
     * Tax info
     * */
    public function getTaxInfoHtml($item){
//        $currency = new Currency();
//        $currencyData['code'] = $currency->getCurrencyCode();
//        $currencyData['currency'] = $currency->getCurrency();
//        $data['currencyData'] = $currencyData;
        $taxPercent = 0;
        if(isset($item['additionalPrice']['tax_details'])){
            $taxdetails = json_decode($item['additionalPrice']['tax_details']);
            foreach ($taxdetails as $key => $taxdetail){
                $taxPercent = $taxPercent+$taxdetail->rate;
            }
        }
        $data['taxPercent'] = $taxPercent;
        $item['additionalPrice']['tax_details'];
        $dataHtml = view('@AxisubsAppTaxes/Site/List/info.twig', compact('item', 'data'));
        if($dataHtml->getStatusCode() == '200'){
            return $dataHtml->getBody();
        } else {
            // echo "Something goes wrong";
        }
    }


    /**
     * load price list in subscription detail page
     * */
    public function loadTaxPriceInSubscriptionPage($subscription){
        $subscriptionMeta = $subscription->meta;
        $subscriptionPrefix = $subscription->ID.'_'.$subscription->post_type.'_';
        if($subscriptionMeta[$subscriptionPrefix.'tax_total'] > 0){
            $tax_details = (array)json_decode($subscriptionMeta[$subscriptionPrefix.'tax_details']);
            $data['tax_details'] = $tax_details;
        }
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $data['currencyData'] = $currencyData;
        $dataHtml = view('@AxisubsAppTaxes/Site/Subscription/price.twig', compact('subscription', 'data'));
        if($dataHtml->getStatusCode() == '200'){
            return $dataHtml->getBody();
        } else {
            // echo "Something goes wrong";
        }
    }

    /**
     * check tax is applicable or not
     * */
    function checkTaxIsApplicable($object, $config){
        $enableTax = 1;
        $configPrefix = $config->ID.'_'.$config->post_type.'_';
        $enable_eu_vat = isset($config->meta[$configPrefix.'enable_eu_vat']) ? $config->meta[$configPrefix.'enable_eu_vat']: 0;
        if($enable_eu_vat) {
            $no_tax_for_non_eu = isset($config->meta[$configPrefix.'no_tax_for_non_eu']) ? $config->meta[$configPrefix.'no_tax_for_non_eu']: 0;
            $apply_digital_rules = isset($config->meta[$configPrefix.'apply_digital_rules']) ? $config->meta[$configPrefix.'apply_digital_rules']: 0;
            $model = $this->getModel();
            $eu_countries = $model->getEUCountries();
            $main_config = HelperConfig::getInstance();
            $country = $main_config->get('country');

            $session = Session();
            $country_id = $session->get('customer_billing_country', $main_config->get('country','') );
            $vat_number = $session->get('customer_billing_vat_number', '' );
            $company = $session->get('customer_billing_company', '' );


            //Rule 1: Home country's individuals and businesses are charged tax
            //Rule 2: EU individuals and businesses with no valid VAT Number are charged tax
            if ($vat_number != '' && $country_id != '') {
                if (in_array($country_id, $eu_countries)) {
                    //Rule 3: EU (non-home country) individuals and businesses with VALID VAT are charged 0 tax
                    if ($country_id != $country) {
                        $validate_vat_number = $model->validateVatNumber($country_id, $vat_number);
                        if ($validate_vat_number) {
                            $enableTax = 0;
                        }
                    }

                }
            }

            // Sub Rule 1: EU individuals (non-home country ) are charged tax
            if ($apply_digital_rules) {
                if (isset($company)) {
                    if ($country_id == $country && $company == '') {
                        $enableTax = 0;
                    }
                } else {
                    if ($country_id == $country) {
                        $enableTax = 0;
                    }
                }
            }

            //Sub Rule 2: Non EU residents are charged 0 percent tax
            if ($no_tax_for_non_eu) {
                if (!in_array($country_id, $eu_countries)) {
                    $enableTax = 0;
                }
            }
        }

        return $enableTax;
    }

    /**
     * Validate Tax number
     * */
    public function validateTaxNumber(&$error, $post){
        $model = $this->getModel();
        $config = $model->getConfig();
        $configPrefix = $config->ID.'_'.$config->post_type.'_';
        $enable_tax = isset($config->meta[$configPrefix.'enable']) ? $config->meta[$configPrefix.'enable']: 0;
        $validate_vat_number = isset($config->meta[$configPrefix.'validate_vat_number']) ? $config->meta[$configPrefix.'validate_vat_number']: 1;
        if($enable_tax && $validate_vat_number){
            $subscribe = $post['axisubs']['subscribe'];
            if(isset($subscribe['vat_number']) && $subscribe['vat_number'] != '' && isset($subscribe['country']) && $subscribe['country'] != ''){
                $eu_countries = $model->getEUCountries();
                if (in_array($subscribe['country'], $eu_countries)) {
                    $validate_vat_number = $model->validateVatNumber($subscribe['country'], $subscribe['vat_number']);
                    if (!$validate_vat_number) {
                        $error['status'] = 1;
                        $fields = $error['fields'];
                        $fields['vat_number'] = 'Invalid Tax Number';
                        $error['fields'] = $fields;
                    }
                }
            }
        }
    }
}