<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace AxisubsAppPaypal\Models\Admin;

use Herbert\Framework\Models\Post;
use Herbert\Framework\Models\PostMeta;
use Herbert\Framework\Http;
use Axisubs\Models\Site\Plans;

class AxisubsAppPaypal extends Post
{
    public $_folder = 'axisubs-app-payment-paypal';
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

    public static $post_type = 'axis_paypal_config';

    public function __construct(array $attributes = [])
    {
    }

    public function getConfig()
    {
        $item = parent::all()->where('post_type', AxisubsAppPaypal::$post_type)->first();
        if(!empty($item)) {
            if ($item->meta() != null) {
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            }
        }
        $this->_item = $item;
        return $this->_item;
    }

    public function getConfigData($fieldName, $default = ''){
        if(empty($this->_item)){
            $this->_item = $this->getConfig();
        }
        if(!empty($this->_item)){
            $key = $this->_item->ID . '_'.AxisubsAppPaypal::$post_type.'_' . $fieldName;
            if(isset($this->_item->meta[$key])){
                if($this->_item->meta[$key] == ''){
                    $result = $default;
                } else {
                    $result = $this->_item->meta[$key];
                }
            } else {
                $result = $default;
            }
        } else {
            $result = $default;
        }

        return $result;
    }

    /**
     * Save Paypal Config
     * */
    public static function saveConfig($post)
    {
        $postDB = \Corcel\Post::where('post_type', AxisubsAppPaypal::$post_type)->get();
        $postTable = $postDB->first();
        if ($postDB->count() == 0) {
            $postTable = new Post();
            $postTable->post_name = 'axisubs_app_paypal_config';
            $postTable->post_title = 'axisubs_app_paypal_config';
            $postTable->post_type = AxisubsAppPaypal::$post_type;
            $postTable->save();
            $postDB = \Corcel\Post::where('post_type', AxisubsAppPaypal::$post_type)->get();
            $postTable = $postDB->first();
        }
        foreach ($post['axisubs']['payment'] as $key => $val) {
            $key = $postTable->ID . '_'.AxisubsAppPaypal::$post_type.'_' . $key;
            $postTable->meta->$key = $val;
        }
        $result = $postTable->save();

        return $result;
    }

    public function processCancelPayment(){
        $planObject = Plans::getInstance();
        $sessionData = Session()->get('axisubs_subscribers');
        $subscription_id  = $sessionData['current_subscription_id'];
        $transaction_data = array(
            'payment_processor' => $this->_folder,
            'processor_status' => 'CANCELED'
        );

        $planObject->paymentCanceled($subscription_id, $transaction_data);

        $returnResult['status'] = 0;
        $returnResult['message'] = 'Canceled payment';
        return $returnResult;
    }

    public function processPaymentReturn(){
        $planObject = Plans::getInstance();
        $http = Http::capture();
        $custom = $http->get('custom');
        $subscription_id  = $custom;
        $data = $http->all();
        $data['transaction_details'] = $this->_getFormattedTransactionDetails($data);
        $this->_log($data['transaction_details']);
        $errors = array();
        if($subscription_id && $subscription_id > 0){
            $subscription = Plans::loadSubscriber($subscription_id);
            $subsPrefix = $subscription->ID.'_axisubs_subscribe_';
            if (empty($subscription)){
                $errors[] = 'Subscription not Found - '.$subscription_id;
                $this->_log($errors);
            }
            // prepare some data
            $validate_ipn = $this->getConfigData('validate_ipn', 1);
            if($validate_ipn) {
                if($subscription && ($subscription->ID == $subscription_id) ) {
                    // validate the IPN info
                    $validation_result = $this->_validateIPN($data, $subscription);
                    if (!empty($validation_result))
                    {
                        // ipn Validation failed
                        $data['ipn_validation_results'] = $validation_result;
                        $errors[] = $validation_result;
                        $this->_log($errors);
                    }

                }
            }

            // process the Payment based on its type
            if ( !empty($data['txn_type']) )
            {
                $known_txn_response_types = ['web_accept', 'subscr_signup', 'subscr_payment', 'subscr_eot'];

                $environment = $this->getConfigData('sandbox', 1);
                // set Payment plugin variables
                if($environment){
                    $merchant_email = trim($this->getConfigData('sandbox_email'));
                }else{
                    $merchant_email = trim($this->getConfigData('merchant_email'));
                }

                // is the recipient correct?
                if (empty ( $data ['receiver_email'] ) || strtolower( $data ['receiver_email'] ) != strtolower( trim ( $merchant_email ) )) {
                    $errors [] = 'Receiver email does not matches';
                    $this->_log($errors);
                }

                // for recurring subscription a recurring profile is created and a confirmation is got for signup
                if ($data['txn_type'] == 'subscr_signup') {
                    // just update the transaction record with profile id and mark pending
                    $transaction_data = array(
                        'payment_processor' => $this->_folder,
                        'subscription_profile_id' => $data ['subscr_id'],
                        'transaction_currency' => $data['mc_currency']
                    );
                    if($subscription->meta[$subsPrefix.'status'] == 'ORDER_PAGE'){
                        $planObject->paymentPending($subscription->ID, $transaction_data);
                    }

                    $returnResult['status'] = 200;
                    $returnResult['message'] = 'Subscription created successfully';
                    return $returnResult;
                }

                if ($data['txn_type'] == 'subscr_payment') {
                    /**
                     * Check if the current subscription record already has a successful transaction object within that date range
                     * if a new or second transaction record comes in, expire the active subscription and create a new subscription for this renewal
                     * associate the txn id with newly created record and activate the subscription
                     * */
                    $current_trans_id = $data['txn_id'];
                    // decide the appropriate subscription record to be processed
                    $next_subscription = $planObject->getNextRenewal( $subscription->ID , $current_trans_id);

                    if ( $next_subscription ) {
                        $subscription = Plans::loadSubscriber($next_subscription);
                        $subsPrefix = $subscription->ID.'_axisubs_subscribe_';
                    }

                }

                // Recurring or Non-recurring subscription Payment confirmation
                if ( in_array($data['txn_type'], array('web_accept', 'subscr_payment' ) ) ) {
                    // a subscription Payment has been done
                    if( !empty($subscription->ID) ) {
                        // check the Payment status
                        if (empty ( $data ['payment_status'] ) || ($data ['payment_status'] != 'Completed' && $data ['payment_status'] != 'Pending')) {
                            $errors [] = 'Invalid Status - '.$data ['payment_status'];
                            $this->_log($errors);
                        }

                        if ( $data['txn_type'] == 'subscr_payment' ) {
                            $gross = $subscription->meta[$subsPrefix.'price']; // TODO : Vefify this
                        }else{
                            $gross = $subscription->meta[$subsPrefix.'total_price']; // TODO : Vefify this
                        }

                        $mc_gross = floatval($data['mc_gross']);

                        //TODO: check the first time Payment and the setup fee with the gross amount
                        if ($mc_gross > 0)
                        {
                            // A positive value means "Payment". The prices MUST match!
                            // Important: NEVER, EVER compare two floating point values for equality.
                            $isValid = ($gross - $mc_gross) < 0.05;
                            if(!$isValid) {
                                $errors[] = 'Payment amount does not matches';
                                $this->_log($errors);
                            }
                        }
                        if(isset($data ['subscr_id'])){
                            $subscr_id = $data ['subscr_id'];
                        } else {
                            $subscr_id = '';
                        }
                        $transaction_data = array(
                            'payment_processor' => $this->_folder,
                            'transaction_ref_id' => $data ['txn_id'],
                            'subscription_profile_id' => $subscr_id,
                            'transaction_amount' => $mc_gross,
                            'transaction_currency' => $data['mc_currency'],
                            'prepayment' => "",
                            'postpayment' => $data['transaction_details'],
                            'authorize' => "",
                            'params' => "",
                            'processor_status' =>$data ['payment_status']
                        );

                        if (count ( $errors )) {
                            $planObject->paymentFailed($subscription->ID, $transaction_data);
                        }elseif (strtoupper($data ['payment_status']) == 'PENDING') {
                            $planObject->paymentPending($subscription->ID, $transaction_data);
                        }elseif(strtoupper($data ['payment_status']) == 'COMPLETED') {
                            $planObject->paymentCompleted($subscription->ID, $transaction_data);
                        }
                    }else{
                        $errors[] = 'Invalid subscription Id';
                        $this->_log($errors);
                    }
                }

                if (  !in_array($data['txn_type'], $known_txn_response_types )  ) {
                    // other methods not supported right now
                    $errors[] = "Invalid transaction type: ".$data['txn_type'];
                    $this->_log($errors);
                }
            }
        } else {
            $errors[] = "Invalid Request: ";
            $this->_log($errors);
        }

        if (count($errors) > 0) {
            $returnResult['status'] = 0;
            $returnResult['message'] = 'Something goes wrong. Please contact site Administrator';
        } else {
            $returnResult['status'] = 200;
            $returnResult['message'] = 'Payment made successfully';
        }

        return $returnResult;
    }

    /**
     * Validates the IPN data
     *
     * @param array $data
     * @return string Empty string if data is valid and an error message otherwise
     * @access protected
     */
    function _validateIPN( $data, $order)
    {
        $paypal_url = $this->getPostURL($this->getConfigData('sandbox', 1));
        $request = 'cmd=_notify-validate';
        foreach ($data as $key => $value) {
            $request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
            //$request .= '&' . $key . '=' . urlencode($value);
        }

        $curl = curl_init($paypal_url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        if (!$response) {
            $this->_log('CURL failed ' . curl_error($curl) . '(' . curl_errno($curl) . ')');
        }

        $this->_log('IPN Validation REQUEST: ' . $request);
        $this->_log('IPN Validation RESPONSE: ' . $response);

        if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0)) {
            return '';
        }elseif (strcmp ($response, 'INVALID') == 0) {
            return 'IPN Validation failed - invalid';
        }
        return '';
    }

    public function getPostURL($sandbox){
        $url = $sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';
        $url = 'https://' . $url . '/cgi-bin/webscr';
        return $url;
    }

    /**
     * Simple logger OVERRIDEEN FOR TETS PURPOSES
     *
     * @param string $text
     * @param string $type
     * @return void
     */
    function _log($text, $type = 'message')
    {
        if (is_array($text) || is_object($text)) {
            $text = json_encode($text);
        }

        $isLog = $this->getConfigData('debug', 1);
        if ($isLog) {
            $file = AXISUBS_APP_PAYPAL_PLUGIN_PATH."logs/payment_log.txt";
            $date = date("Y-m-d g:i:s");
            if(is_writable($file)) {
                $f = fopen($file, 'a');
                fwrite($f, "\n\n" . $date);
                fwrite($f, "\n" . $type . ': ' . $text);
                fclose($f);
            }
        }
    }

    /**
     * Formatts the Payment data for storing
     *
     * @param array $data
     * @return string
     */
    function _getFormattedTransactionDetails( $data )
    {
        return json_encode($data);
    }
}