<?php
/**
 * Specialist Add-On class designed for use by Add-Ons that collect payment
 *
 * @package GFPaymentAddOn
 *
 * NOTE: This class is still undergoing development and is not ready to be used on live sites.
 */

require_once('class-gf-feed-addon.php' );
abstract class GFPaymentAddOn extends GFFeedAddOn {

    private $_payment_version = "1.0";


    /**
     * If set to true, user will not be able to create feeds for a form until a credit card field has been added.
     * @var bool
     */
    protected $_requires_credit_card = false;

    protected $authorization = array();


    //--------- Initialization ----------
    public function pre_init(){
        parent::pre_init();

        // Intercepting callback requests
        add_action( 'parse_request', array( $this, 'maybe_process_callback' ) );

        if ($this->payment_method_is_overridden("check_status"))
            $this->setup_cron();

    }

    public function init_admin() {

        parent::init_admin();

        //enables credit card field
        add_filter("gform_enable_credit_card_field", "__return_true");

        add_filter("gform_currencies", array($this, "supported_currencies"));

        if(rgget("page") == "gf_entries"){
            add_action('gform_entry_info',array($this, "entry_info"), 10, 2);
        }
    }

    public function init_frontend(){

        parent::init_frontend();

        add_filter("gform_confirmation", array($this, "confirmation"), 20, 4);

        add_filter("gform_validation", array($this, "validation"));
        add_action("gform_entry_post_save", array($this, "entry_post_save"), 10, 2);
    }

    public function init_ajax(){
        parent::init_ajax();

        add_action('wp_ajax_gaddon_cancel_subscription', array($this, 'start_cancel_subscription'));

    }

    protected function setup(){
        parent::setup();

        //upgrading Feed Add-On base class
        $installed_version = get_option("gravityformsaddon_payment_version");
        if ($installed_version != $this->_payment_version)
            $this->upgrade_payment($installed_version);

        update_option("gravityformsaddon_payment_version", $this->_payment_version);
    }

    private function upgrade_payment($previous_version) {
        global $wpdb;

        $charset_collate = GFFormsModel::get_db_charset();

        $sql = "CREATE TABLE {$wpdb->prefix}gf_addon_payment_transaction (
                  id int(10) unsigned not null auto_increment,
                  lead_id int(10) unsigned not null,
                  transaction_type varchar(30) not null,
                  transaction_id varchar(50),
                  is_recurring tinyint(1) not null default 0,
                  amount decimal(19,2),
                  date_created datetime,
                  PRIMARY KEY  (id),
                  KEY lead_id (lead_id),
                  KEY trasanction_type (transaction_type),
                  KEY type_lead (lead_id,transaction_type)
                ) $charset_collate;";

        GFFormsModel::dbDelta($sql);
    }

    //--------- Submission Process ------
    public function confirmation($confirmation, $form, $entry, $ajax){

        if(!$this->payment_method_is_overridden('redirect_url'))
            return $confirmation;

        $feed = $this->get_payment_feed($entry, $form);

        if(!$feed)
            return $confirmation;

        $submission_data = $this->get_submission_data($feed, $form, $entry);

        $url = $this->redirect_url($feed, $submission_data, $form, $entry);

        if($url)
            $confirmation = array("redirect" => $url);

        return $confirmation;
    }

    /**
     * Override this function to specify a URL to the third party payment processor. Useful when developing a payment gateway that processes the payment outsite of the website (i.e. PayPal Standard).
     * @param $feed - Active payment feed containing all the configuration data
     * @param $submission_data - Contains form field data submitted by the user as well as payment information (i.e. payment amount, setup fee, line items, etc...)
     * @param $form - Current form array containing all form settings
     * @param $entry - Current entry array containing entry information (i.e data submitted by users)
     * @return string - Return a full URL (inlucing http:// or https://) to the payment processor
     */
    protected function redirect_url($feed, $submission_data, $form, $entry){

    }

    public function validation( $validation_result ) {

        if( ! GFFormDisplay::is_last_page( $validation_result['form'] ) )
            return $validation_result;

        $has_authorize = $this->payment_method_is_overridden('authorize');
        $has_subscribe = $this->payment_method_is_overridden('subscribe');
        if(!$has_authorize && !$has_subscribe)
            return $validation_result;

        //Getting submission data
        $form = $validation_result["form"];
        $entry = GFFormsModel::create_lead($form);
        $feed = $this->get_payment_feed($entry, $form);

        if(!$feed)
            return $validation_result;

        $do_authorization = $has_authorize && $feed["meta"]["transactionType"] == "product";
        $do_subscription = $has_subscribe && $feed["meta"]["transactionType"] == "subscription";

        if(!$do_authorization && !$do_subscription)
            return $validation_result;

        $submission_data = $this->get_submission_data($feed, $form, $entry);

        //Running an authorization only transaction if function is implemented and this is a single payment
        if($do_authorization){
            $this->authorization = $this->authorize($feed, $submission_data, $form, $entry);
        }
        else if($do_subscription){

            $subscription = $this->subscribe( $feed, $submission_data, $form, $entry );

            $this->authorization["is_authorized"] = $subscription["is_success"];
            $this->authorization["error_message"] = rgar($subscription, "error_message");
            $this->authorization["subscription"] = $subscription;

        }


        $this->authorization["feed"] = $feed;
        $this->authorization["submission_data"] = $submission_data;

        if(!$this->authorization["is_authorized"]){
            $validation_result = $this->get_validation_result($validation_result, $this->authorization);

            //Setting up current page to point to the credit card page since that will be the highlighted field
            GFFormDisplay::set_current_page($validation_result["form"]["id"], $validation_result["credit_card_page"]);
        }

        return $validation_result;
    }

    /**
     * Override this method to add integration code to the payment processor in order to authorize a credit card with or without capturing payment. This method is executed during the form validation process and allows
     * the form submission process to fail with a validation error if there is anything wrong with the payment/authorization. This method is only supported by single payments.
     * For subscriptions or recurring payments, use the subscribe() method.
     * @param $feed - Current configured payment feed
     * @param $submission_data - Contains form field data submitted by the user as well as payment information (i.e. payment amount, setup fee, line items, etc...)
     * @param $form - Current form array containing all form settings
     * @param $entry - Current entry array containing entry information (i.e data submitted by users). NOTE: the entry hasn't been saved to the database at this point, so this $entry object does not have the "ID" property and is only a memory representation of the entry.
     * @return array - Return an $authorization array in the following format:
     * [
     *  "is_authorized" => true|false,
     *  "error_message" => "Error message",
     *  "transaction_id" => "XXX",
     *
     *  //If the payment is captured in this method, return a "captured_payment" array with the following information about the payment
     *  "captured_payment" => ["is_success"=>true|false, "error_message" => "error message", "transaction_id" => "xxx", "amount" => 20]
     * ]
     */
    protected function authorize($feed, $submission_data, $form, $entry){

    }

    /**
     * Override this method to capture a single payment that has been authorized via the authorize() method. Use only with single payments. For subscriptions, use subscribe() instead.
     * @param $authorization - Contains the result of the authorize() function
     * @param $feed - Current configured payment feed
     * @param $submission_data - Contains form field data submitted by the user as well as payment information (i.e. payment amount, setup fee, line items, etc...)
     * @param $form - Current form array containing all form settings
     * @param $entry - Current entry array containing entry information (i.e data submitted by users).
     * @return array - Return an array with the information about the captured payment in the following format:
        [
     *      "is_success"=>true|false,
     *      "error_message" => "error message",
     *      "transaction_id" => "xxx",
     *      "amount" => 20,
     *      "payment_method" => "Visa"
     *  ]
     */
    protected function capture($authorization, $feed, $submission_data, $form, $entry){

    }

    /**
     * Override this method to add integration code to the payment processor in order to create a subscription. This method is executed during the form validation process and allows
     * the form submission process to fail with a validation error if there is anything wrong when creating the subscription.
     * @param $feed - Current configured payment feed
     * @param $submission_data - Contains form field data submitted by the user as well as payment information (i.e. payment amount, setup fee, line items, etc...)
     * @param $form - Current form array containing all form settings
     * @param $entry - Current entry array containing entry information (i.e data submitted by users). NOTE: the entry hasn't been saved to the database at this point, so this $entry object does not have the "ID" property and is only a memory representation of the entry.
     * @return array - Return an $subscription array in the following format:
     * [
     *  "is_success"=>true|false,
     *  "error_message" => "error message",
     *  "subscription_id" => "xxx",
     *  "amount" => 10
     *
     *  //To implement an initial/setup fee for gateways that don't support setup fees as part of subscriptions, manually capture the funds for the setup fee as a separate transaction and send that payment
     *  //information in the following "captured_payment" array
     *  "captured_payment" => ["name" => "Setup Fee", "is_success"=>true|false, "error_message" => "error message", "transaction_id" => "xxx", "amount" => 20]
     * ]
     */
    protected function subscribe($feed, $submission_data, $form, $entry){

    }


    protected function get_validation_result($validation_result, $authorization_result){

        $credit_card_page = 0;
        foreach($validation_result["form"]["fields"] as &$field)
        {
            if($field["type"] == "creditcard")
            {
                $field["failed_validation"] = true;
                $field["validation_message"] = $authorization_result["error_message"];
                $credit_card_page = $field["pageNumber"];
                break;
            }
        }

        $validation_result["credit_card_page"] = $credit_card_page;
        $validation_result["is_valid"] = false;

        return $validation_result;

    }

    public function entry_post_save($entry, $form){

        //Abort if authorization wasn't done.
        if(empty($this->authorization))
            return $entry;

        $feed = $this->authorization["feed"];

        if($feed["meta"]["transactionType"] == "product"){

            if($this->payment_method_is_overridden('capture') && rgempty("captured_payment", $this->authorization)){
                $capture_response = $this->capture($this->authorization, $feed, $this->authorization["submission_data"], $form, $entry);
                $this->authorization["captured_payment"] = $capture_response;
            }

            $this->process_capture($this->authorization, $feed, $this->authorization["submission_data"], $form, $entry);

        }
        else if($feed["meta"]["transactionType"] == "subscription"){

            $this->process_subscription($this->authorization, $feed, $this->authorization["submission_data"], $form, $entry);

        }

        gform_update_meta($entry["id"], "payment_gateway", $this->_slug);

        return $entry;
    }

    protected function process_capture($authorization, $feed, $submission_data, $form, $entry){

        $payment = rgar($authorization,"captured_payment");
        if(empty($payment))
            return;

        if($payment["is_success"]){

            $entry["transaction_id"] = $payment["transaction_id"];
            $entry["transaction_type"] = "1";
            $entry["is_fulfilled"] = true;
            $entry["currency"] = GFCommon::get_currency();
            $entry["payment_amount"] = $payment["amount"];
            $entry["payment_status"] = "Paid";
            $entry["payment_date"] = gmdate("Y-m-d H:i:s");
            $entry["payment_method"] = $payment["payment_method"];

            $this->insert_transaction($entry["id"], "payment", $entry["transaction_id"], $entry["payment_amount"]);

            GFFormsModel::add_note($entry["id"], 0, "System", sprintf(__("Payment has been captured successfully. Amount: %s. Transaction Id: %s", "gravityforms"), GFCommon::to_money($payment["amount"], $entry["currency"]),$payment["transaction_id"]));
        }
        else{
            $entry["payment_status"] = "Failed";
            GFFormsModel::add_note($entry["id"], 0, "System", sprintf( __("Payment failed to be captured. Reason: %s", "gravityforms") , $payment["error_message"] ));
        }

        GFAPI::update_entry($entry);

        return $entry;

    }

    protected function process_subscription($authorization, $feed, $submission_data, $form, $entry){

        $subscription = rgar( $authorization, 'subscription' );
        if( empty( $subscription ) )
            return;

        // if setup fee / trial is captured as part of a separate transaction
        $payment = rgar( $subscription, 'captured_payment' );
        $payment_name = rgempty( 'name', $payment ) ? __( 'Initial payment', 'gravityforms' ) : $payment['name'];

        if( $payment && $payment['is_success'] ) {

            $this->insert_transaction($entry['id'], 'payment', $payment['transaction_id'], $payment['amount'], false );

            $amount_formatted = GFCommon::to_money( $payment['amount'], $entry['currency'] );
            $note = sprintf( __( '%s has been captured successfully. Amount: %s. Transaction Id: %s', 'gravityforms' ), $payment_name, $amount_formatted, $payment['transaction_id'] );
            $this->add_note( $entry['id'], $note );

        }
        else if( $payment && ! $payment['is_success'] ) {

            $this->add_note( $entry['id'], sprintf( __( 'Failed to capture %s. Reason: %s.', 'gravityforms' ), $payment['error_message'], $payment_name ) );

        }

        // updating subscription information
        if( $subscription['is_success'] ) {

            $this->start_subscription( $entry, $subscription );

        }
        else {

            $entry['payment_status'] = 'Failed';
            GFAPI::update_entry( $entry );

            $this->add_note( $entry['id'], sprintf( __( 'Subscription failed to be created. Reason: %s', 'gravityforms' ), $subscription['error_message'] ) );

        }

    }



    protected function insert_transaction( $entry_id, $transaction_type, $transaction_id, $amount, $is_recurring = null ) {
        global $wpdb;

        // @todo: make sure stats does not show setup fee as a recurring payment
        $payment_count = $wpdb->get_var($wpdb->prepare("SELECT count(id) FROM {$wpdb->prefix}gf_addon_payment_transaction WHERE lead_id=%d", $entry_id));
        $is_recurring = $payment_count > 0 && $transaction_type == "payment" ? 1 : 0;

        $sql = $wpdb->prepare(" INSERT INTO {$wpdb->prefix}gf_addon_payment_transaction (lead_id, transaction_type, transaction_id, amount, is_recurring, date_created)
                                values(%d, %s, %s, %f, %d, utc_timestamp())", $entry_id, $transaction_type, $transaction_id, $amount, $is_recurring);
        $wpdb->query($sql);

        return $wpdb->insert_id;
    }

    public function get_payment_feed( $entry, $form = false ) {

        $submission_feed = GFCache::get( 'payment_feed' );

        if( ! $submission_feed ) {

            if( $entry['id'] ) {
                $feeds = $this->get_feeds_by_entry( $entry['id'] );
                $submission_feed = $this->get_feed( $feeds[0] );
            }
            else if( $form ) {
                // getting all active feeds
                $feeds =  $this->get_feeds( $form['id'] );

                foreach ( $feeds as $feed ) {
                    if ( $this->is_feed_condition_met( $feed, $form, $entry ) ){
                        $submission_feed = $feed;
                        break;
                    }
                }
            }

            // if called without $form, there is assumption that cache has already been set; let's avoid issues where the cache has not been set and form was not provided
            // so that the cache will only be set when an $entry and $form object are provided
            if( $entry && $form )
                GFCache::set( 'payment_feed', $submission_feed );

        }

        return $submission_feed;
    }

    protected function is_payment_gateway($entry_id){
        $feeds = $this->get_feeds_by_entry($entry_id);
        return is_array($feeds) && count($feeds) > 0;
    }

    protected function get_submission_data($feed, $form, $entry){

        $form_data = array();

        $form_data["form_title"] = $form["title"];

        //getting mapped field data
        $billing_fields = $this->billing_info_fields();
        foreach($billing_fields as $billing_field){
            $field_name = $billing_field["name"];
            $form_data[$field_name] = rgpost('input_'. str_replace(".", "_", rgar($feed["meta"],"billingInformation_{$field_name}") ));
        }

        //getting credit card field data
        $card_field = $this->get_credit_card_field($form);
        if($card_field){

            $form_data["card_number"] = rgpost("input_{$card_field["id"]}_1");
            $form_data["card_expiration_date"] = rgpost("input_{$card_field["id"]}_2");
            $form_data["card_security_code"] = rgpost("input_{$card_field["id"]}_3");
            $form_data["card_name"] = rgpost("input_{$card_field["id"]}_5");

        }

        //getting product field data
        $order_info = $this->get_order_data($feed, $form, $entry);
        $form_data = array_merge($form_data, $order_info);

        return $form_data;
    }

    protected function get_credit_card_field( $form ) {
        $fields = GFCommon::get_fields_by_type($form, array("creditcard"));
        return empty($fields) ? false : $fields[0];
    }

    protected function has_credit_card_field( $form ) {
        return $this->get_credit_card_field( $form ) !== false;
    }

    private function get_order_data($feed, $form, $entry){

        $products = GFCommon::get_product_fields($form, $entry);

        $payment_field = $feed["meta"]["transactionType"] == "product" ? $feed["meta"]["paymentAmount"] : $feed["meta"]["recurringAmount"];
        $setup_fee_field = rgar($feed["meta"],"setupFee_enabled") ? $feed["meta"]["setupFee_product"] : false;
        $trial_field = rgar($feed["meta"], "trial_enabled") ? rgars( $feed, 'meta/trial_product' ) : false;

        $amount = 0;
        $line_items = array();
        $fee_amount = 0;
        $trial_amount = 0;
        foreach($products["products"] as $field_id => $product)
        {

            $quantity = $product["quantity"] ? $product["quantity"] : 1;
            $product_price = GFCommon::to_number($product['price']);

            $options = array();
            if(is_array(rgar($product, "options"))){
                foreach($product["options"] as $option){
                    $options[] = $option["option_name"];
                    $product_price += $option["price"];
                }
            }

            if(!empty($trial_field) && $trial_field == $field_id){
                $trial_amount = $product_price * $quantity;
            }
            else if(!empty($setup_fee_field) && $setup_fee_field == $field_id){
                $fee_amount = $product_price * $quantity;
            }
            else
            {
                if(is_numeric($payment_field) && $payment_field != $field_id)
                    continue;

                $amount += $product_price * $quantity;

                $description = "";
                if(!empty($options))
                    $description = __("options: ", "gravityforms") . " " . implode(", ", $options);

                if($product_price >= 0){
                    $line_items[] = array("id" => $field_id, "name"=>$product["name"], "description" =>$description, "quantity" =>$quantity, "unit_price"=>GFCommon::to_number($product_price));
                }
            }
        }

        if(!empty($products["shipping"]["name"]) && !is_numeric($payment_field)){
            $line_items[] = array("id" => "", "name"=>$products["shipping"]["name"], "description" =>"", "quantity" =>1, "unit_price"=>GFCommon::to_number($products["shipping"]["price"]));
            $amount += $products["shipping"]["price"];
        }

        return array("payment_amount" => $amount, "setup_fee" => $fee_amount, "trial" => $trial_amount, "line_items" => $line_items);
    }


    //--------- Callback (aka Webhook)----------------

    public function maybe_process_callback() {

        // ignoring requests that are not this addon's callbacks
        if( rgget( 'callback' ) != $this->_slug )
            return;

        // returns either false or an array of data about the callback request which payment add-on will then use
        // to generically process the callback data
        $result = $this->callback();
        if( is_array( $result ) && rgar( $result, 'type' ) ) {
            $result = $this->process_callback_action( $result );
        }

        if( is_wp_error( $result ) ) {
            status_header( 500 );
            echo $result->get_error_message();
        }
        else {
            status_header( 200 );
            echo 'Callback processed successfully.';
        }

        die();
    }

    /**
     * Processes callback based on provided data.
     *
     * $action = array(
     *     'type' => 'cancel_subscription',     // required
     *     'transaction_id' => '',              // required (if payment)
     *     'subscription_id' => '',             // required (if subscription)
     *     'amount' => '0.00',                  // required (some exceptions)
     *     'entry_id' => 1,                     // required (some exceptions)
     *     'transaction_type' => '',
     *     'payment_status' => '',
     *     'note' => ''
     * );
     *
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function process_callback_action( $action ) {

        $action = wp_parse_args( $action, array(
            'type' => false,
            'amount' => false,
            'transaction_type' => false,
            'transaction_id' => false,
            'subscription_id' => false,
            'entry_id' => false,
            'payment_status' => false,
            'note' => false
        ) );

        $result = false;

        $entry = GFAPI::get_entry( $action['entry_id'] );
        if( ! $entry )
            return $result;

        switch( $action['type'] ) {
        case 'complete_payment':
            $result = $this->complete_payment( $entry, $action );
            break;
        case 'refund_payment':
            $result = $this->refund_payment( $entry, $action );
            break;
        case 'cancel_subscription':
            $feed = $this->get_payment_feed( $entry );
            $result = $this->cancel_subscription( $entry, $feed, $action["note"] );
            break;
        case 'add_subscription_payment':
            $result = $this->add_subscription_payment( $entry, $action );
            break;
        case 'fail_subscription_payment':
            $result = $this->fail_subscription_payment( $entry, $action );
            break;
        default:
            // handle custom events
            if( is_callable( array( $this, rgar( $action, 'callback' ) ) ) )
                $result = call_user_func( array( $this, $action['callback'] ) );
            break;
        }

        return $result;
    }

    protected function callback() { }



    // # PAYMENT INTERACTION FUNCTIONS

    public function complete_payment( $entry, $action ) {

        if( ! $action['payment_status'] )
            $action['payment_status'] = 'Paid';

        if( ! $action['transaction_type'] )
            $action['transaction_type'] = 'payment';

        if( ! $action['note'] ) {
            $amount_formatted = GFCommon::to_money( $action['amount'], $entry['currency'] );
            $action['note'] = sprintf( __( 'Payment has been paid. Amount: %s. Transaction Id: %s.', 'gravityforms' ), $amount_formatted, $action['transaction_id'] );
        }

        GFAPI::update_entry_property( $entry['id'], 'payment_status', $action['payment_status'] );
        $this->insert_transaction( $entry['id'], $action['transaction_type'], $action['transaction_id'], $action['amount'] );
        $this->add_note( $entry['id'], $action['note'] );

        return true;
    }

    public function refund_payment( $entry, $action ) {

        if( ! $action['payment_status'] )
            $action['payment_status'] = 'Refunded';

        if( ! $action['transaction_type'] )
            $action['transaction_type'] = 'refund';

        if( ! $action['note'] ) {
            $amount_formatted = GFCommon::to_money( $action['amount'], $entry['currency'] );
            $action['note'] = sprintf( __( 'Payment has been refunded. Amount: %s. Transaction Id: %s.', 'gravityforms' ), $amount_formatted, $action['transaction_id'] );
        }

        GFAPI::update_entry_property( $entry['id'], 'payment_status', $action['payment_status'] );
        $this->insert_transaction( $entry['id'], $action['transaction_type'], $action['transaction_id'], $action['amount'] );
        $this->add_note( $entry['id'], $action['note'] );

        return true;
    }

    /**
     * Used to start a new subscription. Updates the associcated entry with the payment and transaction details and adds an entry note.
     *
     * @param  [array]  $entry           Entry object
     * @param  [string] $subscription_id ID of the subscription
     * @param  [float]  $amount          Numeric amount of the initial subscription payment
     * @return [bool]   $result
     */
    public function start_subscription( $entry, $subscription )  {

        $entry['payment_status']   = 'Active';
        $entry['payment_amount']   = $subscription['amount'];
        $entry['payment_date']     = gmdate( 'y-m-d H:i:s' );
        $entry['transaction_id']   = $subscription['subscription_id'];
        $entry['transaction_type'] = '2'; // subscription
        $entry['is_fulfilled']     = true;

        $result = GFAPI::update_entry( $entry );

        $this->add_note( $entry['id'], sprintf( __( 'Subscription has been created. Subscriber Id: %s.', 'gravityforms' ), $subscription['subscription_id'] ) );

    }

    /**
     * A payment on an existing subscription.
     *
     * @param  [array] $data  Transaction data including 'amount' and 'subscriber_id'
     * @param  [array] $entry Entry object
     * @return [null]
     */
    public function add_subscription_payment( $entry, $action ) {

        if( ! $action['transaction_type'] )
            $action['transaction_type'] = 'payment';

        if( ! $action['note'] ) {
            $amount_formatted = GFCommon::to_money( $action['amount'], $entry['currency'] );
            $action['note'] = sprintf( __( 'Subscription payment has been made. Amount: %s. Subscriber Id: %s', 'gravityforms' ), $amount_formatted, $action['subscription_id'] );
        }

        $this->insert_transaction( $entry['id'], $action['transaction_type'], $action['subscription_id'], $action['amount'] );
        $this->add_note( $entry['id'], $action['note'] );

        do_action( 'gform_post_add_subscription_payment', $entry, $action );

        return true;
    }

    public function fail_subscription_payment( $entry, $action ) {

        if( ! $action['note'] ) {
            $amount_formatted = GFCommon::to_money( $action['amount'], $entry['currency'] );
            $action['note'] = sprintf( __( 'Subscription payment has failed. Amount: %s. Subscriber Id: %s.', 'gravityforms' ), $amount_formatted, $action['subscription_id'] );
        }

        $this->add_note( $entry['id'], $action['note'] );

        // keep 'gform_subscription_payment_failed' for backward compatability
        do_action( 'gform_subscription_payment_failed', $entry, $action['subscription_id'] );
        do_action( 'gform_post_fail_subscription_payment', $entry, $action );

        return true;
    }

    public function cancel_subscription( $entry, $feed, $note = null ) {

        if( !$note )
            $note = sprintf( __( 'Subscription has been cancelled. Subscription Id: %s.', 'gravityforms' ), $entry['transaction_id'] );

        if( strtolower( $entry['payment_status'] ) == 'cancelled' ) {
            $this->log( 'Subscription is already canceled.' );
            return false;
        }

        GFAPI::update_entry_property( $entry['id'], 'payment_status', "Cancelled" );
        $this->modify_post( rgar( $entry, 'post_id' ), rgars( $feed, 'meta/update_post_action' ) );
        $this->add_note( $entry['id'], $note );

        // include $subscriber_id as 3rd parameter for backwards compatability
        do_action( 'gform_subscription_canceled', $entry, $feed, $entry['transaction_id'] );

        return true;
    }

    protected function get_entry_by_transaction_id( $transaction_id ) {
        global $wpdb;

        $sql = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}rg_lead WHERE transaction_id = %s", $transaction_id );
        $entry_id = $wpdb->get_var( $sql );

        return $entry_id ? $entry_id : false;
    }




    // -------- Cron --------------------
    protected function setup_cron()
    {
        // Setting up cron
        $cron_name = "{$this->_slug}_cron";

        add_action($cron_name, array($this, "check_status"));

        if (!wp_next_scheduled($cron_name))
            wp_schedule_event(time(), "daily", $cron_name);


    }

    public function check_status(){

    }

    //--------- List Columns ------------
    protected function feed_list_columns() {
        return array(
            'feedName' => __( 'Name', 'gravityforms' ),
            'transactionType' => __('Transaction Type', 'gravityforms'),
            'amount' => __('Amount', 'gravityforms')
        );
    }

    public function get_column_value_transactionType($feed){
        switch(rgar($feed["meta"], "transactionType")){
            case "subscription" :
                return __("Subscription", "gravityforms");
                break;
            case "product" :
                return __("Products and Services", "gravityforms");
                break;

        }
        return __("Unsupported transaction type", "gravityforms");
    }

    public function get_column_value_amount($feed){
        $form = $this->get_current_form();
        $field_id = $feed["meta"]["transactionType"] == "subscription" ? $feed["meta"]["recurringAmount"] : $feed["meta"]["paymentAmount"];
        if($field_id == "form_total"){
            $label = __("Form Total", "gravityforms");
        }
        else{
            $field = GFFormsModel::get_field($form, $field_id);
            $label = GFCommon::get_label($field);
        }

        return $label;
    }


    //--------- Feed Settings ----------------

    public function feed_list_message(){

        if( $this->_requires_credit_card && ! $this->has_credit_card_field( $this->get_current_form() ) ) {
            return $this->requires_credit_card_message();
        }
        return false;
    }

    public function requires_credit_card_message() {
        return sprintf(__( "You must add a Credit Card field to your form before creating a feed. Let's go %sadd one%s!", 'gravityforms' ), "<a href='" . add_query_arg( array("view"=>null, "subview" => null) ) . "'>", "</a>");
    }

    public function feed_settings_fields() {

        return array(

            array(
                //"title" => __("General Settings", "gravityformsstripe"),
                "description" => '',
                "fields" => array(
                    array(
                        "name" => "feedName",
                        "label" => __("Name", "gravityforms"),
                        "type" => "text",
                        "required" => true
                    ),
                    array(
                        "name"=> "transactionType",
                        "label" => __("Transaction Type", "gravityforms"),
                        "type" => "select",
                        "onchange" => "jQuery(this).parents('form').submit();", //@TODO: move this to base class
                        "choices" => array(
                            array("label" => __("Select a transaction type", "gravityforms"), "value" => ""),
                            array("label" => __("Products and Services", "gravityforms"), "value" => "product"),
                            array("label" => __("Subscription", "gravityforms"), "value" => "subscription")
                        )
                    )
                )
            ),

            array(
                'title' => 'Subscription Settings',
                'dependency' => array(
                    "field" => "transactionType",
                    "values" => array("subscription")
                ),
                'fields' => array(
                    array(
                        "name" => "recurringAmount",
                        "label" => __("Recurring Amount", "gravityforms"),
                        "type" => "select",
                        "choices" => $this->recurring_amount_choices(),
                        "required" => true
                    ),
                    array(
                        "name" => "billingCycle",
                        "label" => __("Billing Cycle", "gravityforms"),
                        "type" => "billing_cycle",
                    ),
                    array(
                        "name" => "recurringTimes",
                        "label" => __("Recurring Times", "gravityforms"),
                        "type" => "select",
                        "choices" => array(array("label" => "infinite", "value" => "0")) + $this->get_numeric_choices(1,100)
                    ),
                    array(
                        "name" => "setupFee",
                        "label" => __("Setup Fee", "gravityforms"),
                        "type" => "setup_fee",
                    ),
                    array(
                        "name" => "trial",
                        "label" => __("Trial", "gravityforms"),
                        "type" => "trial",
                        "hidden" => $this->get_setting("setupFee_enabled")
                    )
                )
            ),

            array(
                'title' => 'Products &amp; Services Settings',
                'dependency' => array(
                    'field' => 'transactionType',
                    'values' => array( 'product' )
                ),
                'fields' => array(
                    array(
                        "name" => "paymentAmount",
                        "label" => __("Payment Amount", "gravityforms"),
                        "type" => "select",
                        "choices" => $this->product_amount_choices(),
                        "required" => true
                    )
                )
            ),

            array(
                'title' => __( 'Other Settings', 'gravityforms' ),
                'dependency' => array(
                    'field' => 'transactionType',
                    'values' => array( 'subscription', 'product' )
                ),
                'fields' => $this->other_settings_fields()
            )

        );
    }

    public function other_settings_fields(){
        $other_settings = array(
            array(
                "name" => "billingInformation",
                "label" => __("Billing Information", "gravityforms"),
                "type" => "field_map",
                "field_map" => $this->billing_info_fields()
            )
        );

        $option_choices = $this->option_choices();
        if(!empty($option_choices)){
            $other_settings[] = array(
                "name" => "options",
                "label" => __("Options", "gravityforms"),
                "type" => "checkbox",
                "choices" => $option_choices
            );
        }

        $other_settings[] = array(
            "name" => "conditionalLogic",
            "label" => __("Conditional Logic", "gravityforms"),
            "type" => "feed_condition"
        );

        return $other_settings;
    }

    public function settings_billing_cycle( $field, $echo = true ) {

        $intervals = $this->supported_billing_intervals();

        //Length drop down
        $interval_keys = array_keys($intervals);
        $first_interval = $intervals[$interval_keys[0]];
        $length_field = array(
            "name" => $field["name"] . "_length",
            "type" => "select",
            "choices" => $this->get_numeric_choices($first_interval["min"], $first_interval["max"])
        );

        $html = $this->settings_select( $length_field, false );

        //Unit drop down
        $choices = array();
        foreach($intervals as $unit => $interval){
            if(!empty($interval))
                $choices[] = array("value" => $unit, "label" => $interval["label"]);
        }

        $unit_field = array(
            "name" => $field["name"] . "_unit",
            "type" => "select",
            "onchange" => "loadBillingLength('" . esc_attr($field["name"]) . "')",
            "choices" => $choices
        );

        $html .= "&nbsp" . $this->settings_select( $unit_field, false );

        $html .= "<script type='text/javascript'>var " . $field["name"] . "_intervals = " . json_encode($intervals) . ";</script>";

        if( $echo )
            echo $html;

        return $html;
    }

    public function settings_setup_fee( $field, $echo = true ) {

        $enabled_field = array(
            "type" => "checkbox",
            "horizontal" => true,
            "choices" => array(
                array(  "label" => __("Enabled", "gravityforms"),
                    "name" => $field["name"] . "_enabled",
                    "value"=>"1",
                    "onchange" => "if(jQuery(this).prop('checked')){jQuery('#{$field["name"]}_product').show('slow'); jQuery('#gaddon-setting-row-trial').hide('slow');} else {jQuery('#{$field["name"]}_product').hide('slow'); jQuery('#gaddon-setting-row-trial').show('slow');}"
                ))
        );

        $html = $this->settings_checkbox( $enabled_field, false );

        $form = $this->get_current_form();

        $is_enabled = $this->get_setting("{$field["name"]}_enabled");

        $product_field = array(
            "name" => $field["name"] . "_product",
            "type" => "select",
            "class" =>  $is_enabled ? "" : "hidden",
            "choices" => $this->get_payment_choices($form)
        );

        $html .= "&nbsp" . $this->settings_select( $product_field, false );

        if( $echo )
            echo $html;

        return $html;
    }

    public function settings_trial( $field, $echo = true ) {

        //--- Enabled field ---
        $enabled_field = array(
            "type" => "checkbox",
            "horizontal" => true,
            "choices" => array(
                array(  "label" => __("Enabled", "gravityforms"),
                    "name" => $field["name"] . "_enabled",
                    "value"=>"1",
                    "onchange" => "if(jQuery(this).prop('checked')){jQuery('#{$field["name"]}_product').show('slow'); } else {jQuery('#{$field["name"]}_product').hide('slow');}"
                ))
        );

        $html = $this->settings_checkbox( $enabled_field, false );

        //--- Select Product field ---
        $form = $this->get_current_form();
        $payment_choices = array_merge($this->get_payment_choices($form), array(array("label" => __("Enter an amount", "gravityforms"), "value" => "enter_amount")));

        $product_field = array(
            "name" => $field["name"] . "_product",
            "type" => "select",
            "class" =>  $this->get_setting("{$field["name"]}_enabled") ? "" : "hidden",
            "onchange" => "if(jQuery(this).val() == 'enter_amount'){jQuery('#{$field["name"]}_amount').show('slow');} else {jQuery('#{$field["name"]}_amount').hide('slow');}",
            "choices" => $payment_choices
        );

        $html .= "&nbsp" . $this->settings_select( $product_field, false );

        //--- Trial Amount field ----
        $amount_field = array(
            "type" => "text",
            "name" => "{$field["name"]}_amount",
            "class" =>  $this->get_setting("{$field["name"]}_product") == "enter_amount" ? "" : "hidden",
        );

        $html .= "&nbsp;" . $this->settings_text($amount_field, false);


        if( $echo )
            echo $html;

        return $html;
    }

    protected function recurring_amount_choices(){
        $form = $this->get_current_form();
        $recurring_choices = $this->get_payment_choices($form);
        $recurring_choices[] = array("label" => __("Form Total", "gravityforms"), "value" => "form_total");

        return $recurring_choices;
    }

    protected function product_amount_choices(){
        $form = $this->get_current_form();
        $product_choices = $this->get_payment_choices($form);
        $product_choices[] = array("label" => __("Form Total", "gravityforms"), "value" => "form_total");

        return $product_choices;
    }

    protected function option_choices(){

        $option_choices = array(
                            array("label" => __("Sample Option", "gravityforms"), "name" => "sample_option", "value" => "sample_option")
        );

        return $option_choices;
    }

    protected function billing_info_fields() {

        $fields = array(
            array("name" => "email", "label" => __("Email", "gravityforms"), "required" => false),
            array("name" => "address", "label" => __("Address", "gravityforms"), "required" => false),
            array("name" => "address2", "label" => __("Address 2", "gravityforms"), "required" => false),
            array("name" => "city", "label" => __("City", "gravityforms"), "required" => false),
            array("name" => "state", "label" => __("State", "gravityforms"), "required" => false),
            array("name" => "zip", "label" => __("Zip", "gravityforms"), "required" => false),
            array("name" => "country", "label" => __("Country", "gravityforms"), "required" => false),
        );

        return $fields;
    }

    public function get_numeric_choices($min, $max){
        $choices = array();
        for($i = $min; $i<=$max; $i++){
            $choices[] = array("label" => $i, "value" => $i);
        }
        return $choices;
    }

    protected function supported_billing_intervals(){

        $billing_cycles = array(
            "day"   => array( "label" => __( "day(s)", "gravityforms" ),   "min" => 1, "max" => 365 ),
            "week"  => array( "label" => __( "week(s)", "gravityforms" ),  "min" => 1, "max" => 52  ),
            "month" => array( "label" => __( "month(s)", "gravityforms" ), "min" => 1, "max" => 12  ),
            "year"  => array( "label" => __( "year(s)", "gravityforms" ),  "min" => 1, "max" => 10  )
        );

        return $billing_cycles;
    }

    protected function get_payment_choices($form){
        $fields = GFCommon::get_fields_by_type($form, array("product"));
        $choices = array(
            array("label" => __("Select a product field", "gravityforms"), "value" => "")
        );

        foreach($fields as $field){
            $field_id = $field["id"];
            $field_label = RGFormsModel::get_label($field);
            $choices[] = array("value" => $field_id, "label" => $field_label);
        }

        return $choices;
    }

    //--------- Stats Page -------------------
    public function get_results_page_config() {
        $current_form = $this->get_current_form();

        if(!$this->has_feed($current_form["id"]))
            return false;

        return array(
            "title"         => "Sales",
            "search_title"  => "Filter",
            "capabilities"  => array("gravityforms_view_entries"),
            "callbacks"     => array(
                "data"      => array($this, "results_data"),
                "markup"    => array($this, "results_markup"),
                "filter_ui" => array($this, "results_filter_ui")
            )
        );

    }

    public function results_markup($html, $data, $form, $fields){

        $html = "<table width='100%' id='gaddon-results-summary'>
                    <tr>
                        <td class='gaddon-results-summary-label'>" . __("Today", "gravityforms") . "</td>
                        <td class='gaddon-results-summary-label'>" . __("Yesterday", "gravityforms") . "</td>
                        <td class='gaddon-results-summary-label'>" . __("Last 30 Days", "gravityforms") . "</td>
                        <td class='gaddon-results-summary-label'>" . __("Total", "gravityforms") . "</td>
                    </tr>
                    <tr>
                        <td class='gaddon-results-summary-data'>
                            <div class='gaddon-results-summary-data-box'>
                                <div class='gaddon-results-summary-primary'>{$data["summary"]["today"]["revenue"]}</div>
                                <div class='gaddon-results-summary-secondary'>{$data["summary"]["today"]["subscriptions"]} " . __("subscriptions", "gravityforms") . "</div>
                                <div class='gaddon-results-summary-secondary'>{$data["summary"]["today"]["orders"]} " . __("orders", "gravityforms") . "</div>
                            </div>
                        </td>
                        <td class='gaddon-results-summary-data'>
                            <div class='gaddon-results-summary-data-box'>
                                <div class='gaddon-results-summary-primary'>{$data["summary"]["yesterday"]["revenue"]}</div>
                                <div class='gaddon-results-summary-secondary'>{$data["summary"]["yesterday"]["subscriptions"]} " . __("subscriptions", "gravityforms") . "</div>
                                <div class='gaddon-results-summary-secondary'>{$data["summary"]["yesterday"]["orders"]} " . __("orders", "gravityforms") . "</div>
                            </div>
                        </td>

                        <td class='gaddon-results-summary-data'>
                            <div class='gaddon-results-summary-data-box'>
                                <div class='gaddon-results-summary-primary'>{$data["summary"]["last30"]["revenue"]}</div>
                                <div class='gaddon-results-summary-secondary'>{$data["summary"]["last30"]["subscriptions"]} " . __("subscriptions", "gravityforms") . "</div>
                                <div class='gaddon-results-summary-secondary'>{$data["summary"]["last30"]["orders"]} " . __("orders", "gravityforms") . "</div>
                            </div>
                        </td>
                        <td class='gaddon-results-summary-data'>
                            <div class='gaddon-results-summary-data-box'>
                                <div class='gaddon-results-summary-primary'>{$data["summary"]["total"]["revenue"]}</div>
                                <div class='gaddon-results-summary-secondary'>{$data["summary"]["total"]["subscriptions"]} " . __("subscriptions", "gravityforms") . "</div>
                                <div class='gaddon-results-summary-secondary'>{$data["summary"]["total"]["orders"]} " . __("orders", "gravityforms") . "</div>
                            </div>
                        </td>

                    </tr>
                 </table>";

        if($data["row_count"] == "0"){
            $html .= "<div class='updated' style='padding:20px; margin-top:40px;'>" . __("There aren't any transactions that match your criteria.", "gravityforms") . "</div>";
        }
        else{
            $chart_data = $this->get_chart_data($data);
            $html .= $this->get_sales_chart($chart_data);

            //Getting sales table markup
            $sales_table = new GFPaymentStatsTable($data["table"]["header"], $data["data"], $data["row_count"], $data["page_size"]);
            $sales_table->prepare_items();
            ob_start();
            $sales_table->display();
            $html .= ob_get_clean();
        }

        $html .= "</form>";

        return $html;
    }

    protected function get_chart_data($data){
        $hAxis_column = $data["chart"]["hAxis"]["column"];
        $vAxis_column = $data["chart"]["vAxis"]["column"];

        $chart_data = array();
        foreach($data["data"] as $row){
            $hAxis_value = $row[$hAxis_column];
            $chart_data[$hAxis_value] = $row[$vAxis_column];
        }

        return array("hAxis_title" => $data["chart"]["hAxis"]["label"], "vAxis_title" => $data["chart"]["vAxis"]["label"], "data" => $chart_data);
    }

    public static function get_sales_chart($sales_data) {
        $markup = "";

        $data_table    = array();
        $data_table[] = array($sales_data["hAxis_title"], $sales_data["vAxis_title"]);

        foreach ($sales_data["data"] as $key => $value) {
            $data_table[] = array((string)$key, $value);
        }

        $chart_options = array(
            'series' => array(
                '0' => array(
                    'color'           => '#66CCFF',
                    'visibleInLegend' => 'false'
                ),
            ),
            'hAxis'  => array(
                'title' => $sales_data["hAxis_title"]
            ),
            'vAxis'  => array(
                'title' => $sales_data["vAxis_title"]
            )
        );

        $data_table_json = json_encode($data_table);
        $options_json    = json_encode($chart_options);
        $div_id          = "gquiz-results-chart-field-score-frequencies";
        $markup .= "<div class='gresults-chart-wrapper' style='width:100%;height:250px' id='{$div_id}'></div>";
        $markup .= "<script>
                        jQuery('#{$div_id}')
                            .data('datatable',{$data_table_json})
                            .data('options', {$options_json})
                            .data('charttype', 'column');
                    </script>";

        return $markup;

    }

    public function results_data($form, $fields, $search_criteria, $state_array) {

        $summary = $this->get_sales_summary($form["id"]);

        $data = $this->get_sales_data($form["id"], $search_criteria, $state_array);

        return array(   "entry_count" => $data["row_count"],
                        "row_count" => $data["row_count"],
                        "page_size" => $data["page_size"],
                        "status" => "complete",
                        "summary" => $summary,
                        "data" => $data["rows"],
                        "chart" => $data["chart"],
                        "table" => $data["table"]
                    );
    }

    private function get_mysql_tz_offset(){
        $tz_offset = get_option("gmt_offset");

        //add + if offset starts with a number
        if(is_numeric(substr($tz_offset, 0, 1)))
            $tz_offset = "+" . $tz_offset;

        return $tz_offset . ":00";
    }

    protected function get_sales_data($form_id, $search, $state){
        global $wpdb;

        $data = array( "chart" => array("hAxis" => array(), "vAxis" => array("column" => "revenue", "label" => __("Revenue", "gravityforms"))),
                       "table" => array("header" => array("orders" => __("Orders", "gravityforms"), "subscriptions" =>__("Subscriptions", "gravityforms"), "recurring_payments" =>__("Recurring Payments", "gravityforms"), "refunds" =>__("Refunds", "gravityforms"), "revenue" => __("Revenue", "gravityforms"))),
                       "rows" => array()
        );

        $tz_offset = $this->get_mysql_tz_offset();

        $page_size = 10;
        $group = strtolower(rgpost('group'));
        switch($group){

            case "weekly" :
                $select = "concat(left(lead.week,4), ' - ', right(lead.week,2)) as week";
                $select_inner1 = "yearweek(CONVERT_TZ(date_created, '+00:00', '" . $tz_offset . "')) week";
                $select_inner2 = "yearweek(CONVERT_TZ(t.date_created, '+00:00', '" . $tz_offset . "')) week";
                $group_by = "week";
                $order_by = "week desc";
                $join = "lead.week = transaction.week";

                $data["chart"]["hAxis"]["column"] = "week";
                $data["chart"]["hAxis"]["label"] = __("Week", "gravityforms");
                $data["table"]["header"] = array_merge(array("week" => __("Week", "gravityforms")), $data["table"]["header"]);
                break;

            case "monthly" :
                $select = "date_format(lead.month, '%%Y') as year, date_format(lead.month, '%%c') as month, '' as month_abbrev, '' as month_year";
                $select_inner1 = "date_format(CONVERT_TZ(date_created, '+00:00', '" . $tz_offset . "'), '%%Y-%%m-01') month";
                $select_inner2 = "date_format(CONVERT_TZ(t.date_created, '+00:00', '" . $tz_offset . "'), '%%Y-%%m-01') month";
                $group_by = "month";
                $order_by = "month desc";
                $join = "lead.month = transaction.month";

                $data["chart"]["hAxis"]["column"] = "month_abbrev";
                $data["chart"]["hAxis"]["label"] = __("Month", "gravityforms");
                $data["table"]["header"] = array_merge(array("month_year" => __("Month", "gravityforms")), $data["table"]["header"]);
                break;

            default : //daily
                $select = "lead.date, date_format(lead.date, '%%c') as month, day(lead.date) as day, dayname(lead.date) as day_of_week, '' as month_day";
                $select_inner1 = "date(CONVERT_TZ(date_created, '+00:00', '" . $tz_offset . "')) as date";
                $select_inner2 = "date(CONVERT_TZ(t.date_created, '+00:00', '" . $tz_offset . "')) as date";
                $group_by = "date";
                $order_by = "date desc";
                $join = "lead.date = transaction.date";

                $data["chart"]["hAxis"]["column"] = "month_day";
                $data["chart"]["hAxis"]["label"] = __("Day", "gravityforms");
                $data["table"]["header"] = array_merge(array("date" => __("Date", "gravityforms"), "day_of_week" => __("Day", "gravityforms")), $data["table"]["header"]);
                break;
        }

        $lead_date_filter = "";
        $transaction_date_filter = "";
        if(isset($search["start_date"])) {
            $lead_date_filter = $wpdb->prepare(" AND timestampdiff(SECOND, %s, CONVERT_TZ(l.date_created, '+00:00', '" . $tz_offset . "')) >= 0", $search["start_date"]);
            $transaction_date_filter = $wpdb->prepare(" AND timestampdiff(SECOND, %s, CONVERT_TZ(t.date_created, '+00:00', '" . $tz_offset . "')) >= 0", $search["start_date"]);
        }

        if(isset($search["end_date"])) {
            $lead_date_filter .= $wpdb->prepare(" AND timestampdiff(SECOND, %s, CONVERT_TZ(l.date_created, '+00:00', '" . $tz_offset . "')) <= 0", $search["end_date"]);
            $transaction_date_filter .= $wpdb->prepare(" AND timestampdiff(SECOND, %s, CONVERT_TZ(t.date_created, '+00:00', '" . $tz_offset . "')) <= 0", $search["end_date"]);
        }

        $payment_method = rgpost("payment_method");
        $payment_method_filter = "";
        if(!empty($payment_method)){
            $payment_method_filter = $wpdb->prepare(" AND l.payment_method=%s", $payment_method);
        }

        $current_page = rgempty("paged") ? 1 : absint(rgpost("paged"));
        $offset = $page_size * ($current_page - 1);

        $sql = $wpdb->prepare(" SELECT SQL_CALC_FOUND_ROWS {$select}, lead.orders, lead.subscriptions, transaction.refunds, transaction.recurring_payments, transaction.revenue
                                FROM (
                                  SELECT  {$select_inner1},
                                          sum( if(transaction_type = 1,1,0) ) as orders,
                                          sum( if(transaction_type = 2,1,0) ) as subscriptions
                                  FROM {$wpdb->prefix}rg_lead l
                                  WHERE form_id=%d {$lead_date_filter} {$payment_method_filter}
                                  GROUP BY {$group_by}
                                ) AS lead

                                LEFT OUTER JOIN(
                                  SELECT  {$select_inner2},
                                          sum(t.amount) as revenue,
                                          sum( if(t.transaction_type = 'refund', 1, 0) ) as refunds,
                                          sum( if(t.transaction_type = 'payment' AND t.is_recurring = 1, 1, 0) ) as recurring_payments
                                  FROM {$wpdb->prefix}gf_addon_payment_transaction t
                                  INNER JOIN {$wpdb->prefix}rg_lead l ON l.id = t.lead_id
                                  WHERE l.form_id=%d {$lead_date_filter} {$transaction_date_filter} {$payment_method_filter}
                                  GROUP BY {$group_by}

                                ) AS transaction on {$join}
                                ORDER BY {$order_by}
                                LIMIT $page_size OFFSET $offset
                                ", $form_id, $form_id);


        $results = $wpdb->get_results($sql, ARRAY_A);
        foreach($results as &$result){
            $result["orders"] = intval($result["orders"]);
            $result["subscriptions"] = intval($result["subscriptions"]);
            $result["refunds"] = intval($result["refunds"]);
            $result["recurring_payments"] = intval($result["recurring_payments"]);
            $result["revenue"] = floatval($result["revenue"]);

            $result = $this->format_chart_h_axis($result);

        }

        $data["row_count"] = $wpdb->get_var("SELECT FOUND_ROWS()");
        $data["page_size"] = $page_size;

        $data["rows"] = $results;

        return $data;

    }

    protected function format_chart_h_axis($result){
        $months = array(__("Jan", "gravityforms"), __("Feb", "gravityforms"), __("Mar", "gravityforms"), __("Apr", "gravityforms") ,__("May", "gravityforms"), __("Jun", "gravityforms"), __("Jul", "gravityforms"), __("Aug", "gravityforms"), __("Sep", "gravityforms"), __("Oct", "gravityforms"), __("Nov", "gravityforms"), __("Dec", "gravityforms"));

        if(isset($result["month_abbrev"])){
            $result["month_abbrev"] = $months[intval($result["month"]) - 1];
            $result["month_year"] = $months[intval($result["month"]) - 1] . ", " . $result["year"];
            return $result;
        }
        else if(isset($result["month_day"])){
            $result["month_day"] = $months[intval($result["month"]) - 1] . " " . $result["day"];
            return $result;
        }

        return $result;
    }

    protected function get_sales_summary($form_id){
        global $wpdb;

        $tz_offset = $this->get_mysql_tz_offset();

        $summary = $wpdb->get_results(
            $wpdb->prepare("
                    SELECT lead.date, lead.orders, lead.subscriptions, transaction.revenue
                    FROM (
                       SELECT  date( CONVERT_TZ(date_created, '+00:00', '" . $tz_offset . "') ) as date,
                               sum( if(transaction_type = 1,1,0) ) as orders,
                               sum( if(transaction_type = 2,1,0) ) as subscriptions
                       FROM {$wpdb->prefix}rg_lead
                       WHERE form_id = %d and datediff(now(), CONVERT_TZ(date_created, '+00:00', '" . $tz_offset . "') ) <= 30
                       GROUP BY date
                     ) AS lead

                     LEFT OUTER JOIN(
                       SELECT  date( CONVERT_TZ(t.date_created, '+00:00', '" . $tz_offset . "') ) as date,
                               sum(t.amount) as revenue
                       FROM {$wpdb->prefix}gf_addon_payment_transaction t
                         INNER JOIN {$wpdb->prefix}rg_lead l ON l.id = t.lead_id
                       WHERE l.form_id=%d
                       GROUP BY date
                     ) AS transaction on lead.date = transaction.date
                    ORDER BY date desc", $form_id, $form_id), ARRAY_A);

        $total_summary = $wpdb->get_results(
            $wpdb->prepare("
                    SELECT sum( if(transaction_type = 1,1,0) ) as orders,
                         sum( if(transaction_type = 2,1,0) ) as subscriptions
                    FROM {$wpdb->prefix}rg_lead
                    WHERE form_id=%d", $form_id), ARRAY_A );

        $total_revenue = $wpdb->get_var(
            $wpdb->prepare("
                    SELECT sum(t.amount) as revenue
                    FROM {$wpdb->prefix}gf_addon_payment_transaction t
                    INNER JOIN {$wpdb->prefix}rg_lead l ON l.id = t.lead_id
                    WHERE l.form_id=%d", $form_id));


        $result = array("today"     => array("revenue" => GFCommon::to_money(0), "orders" => 0, "subscriptions" => 0),
                        "yesterday" => array("revenue" => GFCommon::to_money(0), "orders" => 0, "subscriptions" => 0),
                        "last30"    => array("revenue" => 0, "orders" => 0, "subscriptions" => 0),
                        "total"     => array("revenue" => GFCommon::to_money($total_revenue), "orders" => $total_summary[0]["orders"], "subscriptions" => $total_summary[0]["subscriptions"]));

        $local_time = GFCommon::get_local_timestamp();
        $today = gmdate("Y-m-d", $local_time);
        $yesterday = gmdate("Y-m-d", strtotime("-1 day", $local_time));

        foreach($summary as $day){
            if($day["date"] == $today){
                $result["today"]["revenue"] = GFCommon::to_money($day["revenue"]);
                $result["today"]["orders"] = $day["orders"];
                $result["today"]["subscriptions"] = $day["subscriptions"];
            }
            else if($day["date"] ==  $yesterday){
                $result["yesterday"]["revenue"] = GFCommon::to_money($day["revenue"]);
                $result["yesterday"]["orders"] = $day["orders"];
                $result["yesterday"]["subscriptions"] = $day["subscriptions"];
            }

            $is_within_30_days = strtotime($day["date"]) >= strtotime($local_time . " -30 days") ;
            if($is_within_30_days){
                $result["last30"]["revenue"] += floatval($day["revenue"]);
                $result["last30"]["orders"] += floatval($day["orders"]);
                $result["last30"]["subscriptions"] += floatval($day["subscriptions"]);
            }
        }

        $result["last30"]["revenue"] = GFCommon::to_money($result["last30"]["revenue"]);

        return $result;
    }

    public function results_filter_ui($filter_ui, $form_id, $page_title, $gf_page, $gf_view){

        if($gf_view == "gf_results_{$this->_slug}")
            unset($filter_ui["fields"]);

        $view_markup = "<div>
                    <select id='gaddon-sales-group' name='group'>
                        <option value='daily' " . selected('daily', rgget('group'), false) . ">" . __("Daily", "gravityforms") . "</option>
                        <option value='weekly' " . selected('weekly', rgget('group'), false) . ">" . __("Weekly", "gravityforms") . "</option>
                        <option value='monthly' " . selected('monthly', rgget('group'), false) . ">" . __("Monthly", "gravityforms") . "</option>
                    </select>
                  </div>";
        $view_filter = array("view" => array("label" => __("View", "gravityforms"), "tooltip" => __("<h6>View</h6>Select how you would like the sales data to be displayed.", "gravityforms"), "markup" => $view_markup));

        $payment_methods = $this->get_payment_methods($form_id);

        $payment_method_markup = "
                <div>
                    <select id='gaddon-sales-group' name='payment_method'>
                        <option value=''>" . __("Any", "gravityforms") . "</option>";

                    foreach($payment_methods as $payment_method){
                        $payment_method_markup .= "<option value='" . esc_attr($payment_method) . "' " . selected($payment_method, rgget('payment_method'), false) . ">" . $payment_method . "</option>";
                    }
        $payment_method_markup .="
                    </select>
                 </div>";

        $payment_method_filter = array("payment_method" => array("label" => __("Payment Method", "gravityforms"), "tooltip" => "", "markup" => $payment_method_markup));


        $filter_ui = array_merge($view_filter, $payment_method_filter, $filter_ui);

        return $filter_ui;

    }

    protected function get_payment_methods($form_id){
        global $wpdb;

        $payment_methods = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT payment_method FROM {$wpdb->prefix}rg_lead WHERE form_id=%d", $form_id));

        return $payment_methods;
    }
    //-------- Uninstall ---------------------
    protected function uninstall(){
        global $wpdb;

        // deleting transactions
        $sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}gf_addon_payment_transaction
                                WHERE lead_id IN
                                   (SELECT lead_id FROM {$wpdb->prefix}rg_lead_meta WHERE meta_key='payment_gateway' AND meta_value=%s)", $this->_slug);

        $wpdb->query($sql);

        //clear cron
        wp_clear_scheduled_hook($this->_slug . "_cron");

        parent::uninstall();
    }

    //-------- Scripts -----------------------
    public function scripts() {

        $scripts = array(
            array(
                'handle' => 'gaddon_payment',
                "src" => $this->get_gfaddon_base_url() . "/js/gaddon_payment.js",
                "version" => GFCommon::$version,
                "strings" => array(
                        "subscriptionCancelWarning" => __("Warning! This Authorize.Net Subscription will be canceled. This cannot be undone. 'OK' to cancel subscription, 'Cancel' to stop", "gravityforms"),
                        "subscriptionCancelNonce" => wp_create_nonce('gaddon_cancel_subscription'),
                        "subscriptionCanceled" => __("Canceled", "gravityforms"),
                        "subscriptionError" => __("The subscription could not be canceled. Please try again later.", "gravityforms")
                        ),
                'enqueue' => array(
                    array( "admin_page" => array( "form_settings" ), "tab" => $this->_slug ),
                    array( "admin_page" => array( "entry_view" ) )
                )
            )
        );

        return array_merge( parent::scripts(), $scripts );
    }


    //-------- Currency ----------------------
    /**
     * Override this function to add or remove currencies from the list of supported currencies
     * @param $currencies - Currently supported currencies
     * @return mixed - A filtered list of supported currencies
     */
    public function supported_currencies($currencies){
        return $currencies;
    }


    //-------- Cancel Subscription -----------
    public function entry_info($form_id, $entry) {

        //abort if subscription cancelation isn't supported by the addon or if it has already been canceled
        if( !$this->payment_method_is_overridden("cancel_subscription") )
            return;

        // adding cancel subscription button and script to entry info section
        $cancelsub_button = "";
        if($entry["transaction_type"] == "2" && $entry["payment_status"] <> "Cancelled" && $this->is_payment_gateway($entry["id"]))
        {
            ?>
            <input id="cancelsub" type="button" name="cancelsub" value="<?php _e("Cancel Subscription", "gravityforms") ?>" class="button" onclick="cancel_subscription(<?php echo $entry["id"] ?>);"/>
            <img src="<?php echo GFCommon::get_base_url() ?>/images/spinner.gif" id="subscription_cancel_spinner" style="display: none;"/>

            <script type="text/javascript">

            </script>

            <?php
        }
    }

    public function start_cancel_subscription() {
        check_ajax_referer("gaddon_cancel_subscription","gaddon_cancel_subscription");

        $entry_id = $_POST["entry_id"];
        $entry = GFAPI::get_entry($entry_id);

        $form = GFAPI::get_form($entry["form_id"]);
        $feed = $this->get_payment_feed($entry, $form);

        if( $this->cancel_subscription( $entry, $feed ) ) {
            die( '1' );
        }
        else {
            die( '0' );
        }

    }










    // # HELPERS

    private function payment_method_is_overridden( $method_name, $base_class = 'GFPaymentAddOn' ){
        return parent::method_is_overridden( $method_name, $base_class );
    }

    public function authorization_error( $error_message ) {
        return array( 'error_message' => $error_message, 'is_success' => false, 'is_authorized' => false );
    }

    protected function modify_post( $post_id, $action ) {

        $result = false;

        if( ! $post_id )
            return $result;

        switch( $action ) {
        case 'draft':
            $post = get_post( $post_id );
            $post->post_status = 'draft';
            $result = wp_update_post( $post );
            $this->log( "Set post (#{$post_id}) status to \"draft\"." );
            break;
        case 'delete':
            $result = wp_delete_post( $post_id );
            $this->log( "Deleted post (#{$post_id})." );
            break;
        }

        return $result;
    }

    public function add_note( $entry_id, $note, $user_id = false ) {

        if( $user_id ) {
            $user = new WP_User( $user_id );
            $user_name = $user->get( 'display_name' );
        } else {
            $user_id = 0;
            $user_name = 'System';
        }

        GFFormsModel::add_note( $entry_id, $user_id, $user_name, $note );

    }

    public function log( $message ) {
        // log it!
    }

}

if( ! class_exists( 'WP_List_Table' ) )
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


class GFPaymentStatsTable extends WP_List_Table {

    private $_rows = array();
    private $_page_size = 10;
    private $_total_items = 0;


    function __construct($columns, $rows, $total_count, $page_size) {
        $this->_rows = $rows;
        $this->_total_items = $total_count;
        $this->_page_size = $page_size;

        $this->_column_headers = array(
            $columns,
            array(),
            array()
        );

        parent::__construct(array(
            'singular' => __('sale', 'gravityforms'),
            'plural'   => __('sales', 'gravityforms'),
            'ajax'     => false,
            'screen'   => 'gaddon_sales'
        ));
    }


    function prepare_items() {
        $this->items = $this->_rows;

        $this->set_pagination_args(array("total_items" => $this->_total_items, "per_page" => $this->_page_size));
    }

    function no_items() {
        echo __("There hasn't been any sales in the specified date range.", "gravityforms");
    }

    function column_default($item, $column){
        return rgar($item, $column);
    }


    function pagination( $which ) {
        if ( empty( $this->_pagination_args ) )
            return;

        extract( $this->_pagination_args, EXTR_SKIP );

        $output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current = $this->get_pagenum();

        $page_links = array();

        $disable_first = $disable_last = '';
        if ( $current == 1 )
            $disable_first = ' disabled';
        if ( $current == $total_pages )
            $disable_last = ' disabled';

        $page_links[] = sprintf( "<a class='%s' title='%s' style='cursor:pointer;' onclick='gresults.setCustomFilter(\"paged\", \"1\"); gresults.getResults();'>%s</a>",
            'first-page' . $disable_first,
            esc_attr__( 'Go to the first page', 'gravityforms' ),
            '&laquo;'
        );

        $page_links[] = sprintf( "<a class='%s' title='%s' style='cursor:pointer;' onclick='gresults.setCustomFilter(\"paged\", \"%s\"); gresults.getResults(); gresults.setCustomFilter(\"paged\", \"1\");'>%s</a>",
            'prev-page' . $disable_first,
            esc_attr__( 'Go to the previous page', 'gravityforms' ),
            max( 1, $current-1 ),
            '&lsaquo;'
        );


        $html_current_page = $current;

        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span>';

        $page_links[] = sprintf( "<a class='%s' title='%s' style='cursor:pointer;' onclick='gresults.setCustomFilter(\"paged\", \"%s\"); gresults.getResults(); gresults.setCustomFilter(\"paged\", \"1\");'>%s</a>",
            'next-page' . $disable_last,
            esc_attr__( 'Go to the next page', 'gravityforms' ),
            min( $total_pages, $current+1 ),
            '&rsaquo;'
        );

        $page_links[] = sprintf( "<a class='%s' title='%s' style='cursor:pointer;' onclick='gresults.setCustomFilter(\"paged\", \"%s\"); gresults.getResults(); gresults.setCustomFilter(\"paged\", \"1\");'>%s</a>",
            'last-page' . $disable_last,
            esc_attr__( 'Go to the last page', 'gravityforms'),
            $total_pages,
            '&raquo;'
        );

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) )
            $pagination_links_class = ' hide-if-js';
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages )
            $page_class = $total_pages < 2 ? ' one-page' : '';
        else
            $page_class = ' no-pages';

        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

}
