<?php

/**
 * Paymentwall Gateway for Jigoshop
 *
 * Description: Official Paymentwall module for WordPress WooCommerce.
 * Plugin URI: https://www.paymentwall.com/en/documentation/Jigoshop-WordPress/1168
 * Author: Paymentwall
 */
class jigoshop_paymentwall extends paymentwall_gateway
{
    public $id = 'paymentwall';
    public $has_fields = false;

    public function __construct()
    {
        parent::__construct();

        $options = Jigoshop_Base::get_options();

        // Prepare configs
        $this->icon = PW_JIGO_PLUGIN_URL . '/assets/images/paymentwall_logo.png';
        $this->enabled = $options->get('jigoshop_paymentwall_enabled');
        $this->title = $options->get('jigoshop_paymentwall_title');
        $this->appkey = $options->get('jigoshop_paymentwall_app_key');
        $this->secretkey = $options->get('jigoshop_paymentwall_secret_key');
        $this->widget = $options->get('jigoshop_paymentwall_widget');
        $this->description = $options->get('jigoshop_paymentwall_description');
        $this->thankyoutext = $options->get('jigoshop_paymentwall_thankyoutext');
        $this->successurl = $options->get('jigoshop_paymentwall_successurl');
        $this->testmode = $options->get('jigoshop_paymentwall_testmode');
        $this->delivery = $options->get('jigoshop_paymentwall_delivery');
        $this->currency = $options->get('jigoshop_currency');

        //$this->notify_url = jigoshop_request_api::query_request('?js-api=jigoshop_paymentwall', false);

        add_action('init', array($this, 'check_ipn_response'));
        add_action('receipt_' . $this->id, array($this, 'receipt_page'));
    }

    function init_paymentwall_configs()
    {
        Paymentwall_Config::getInstance()->set(array(
            'api_type' => Paymentwall_Config::API_GOODS,
            'public_key' => $this->appkey, // available in your Paymentwall merchant area
            'private_key' => $this->secretkey // available in your Paymentwall merchant area
        ));
    }

    function receipt_page($order_id)
    {
        $this->init_paymentwall_configs();
        $order = new jigoshop_order($order_id);

        $widget = new Paymentwall_Widget(
            ($order->user_id == 0) ? $_SERVER['REMOTE_ADDR'] : $order->user_id,    // id of the end-user who's making the payment
            $this->widget,                                            // widget code, e.g. p1; can be picked inside of your merchant account
            array(                                                    // product details for Flexible Widget Call. To let users select the product on Paymentwall's end, leave this array empty
                new Paymentwall_Product(
                    $order->id,                                // id of the product in your system
                    $order->order_subtotal,                    // price
                    $this->currency,                        // currency code
                    'Order #' . $order->id,            // product name
                    Paymentwall_Product::TYPE_FIXED            // this is a time-based product; for one-time products, use Paymentwall_Product::TYPE_FIXED and omit the following 3 array elements
                )
            ),
            array_merge(
                array(
                    'email' => $order->billing_email,
                    'success_url' => $this->successurl,
                    'test_mode' => (int)$this->testmode == 'yes' ? 1 : 0,
                    'integration_module' => 'jigoshop'
                ),
                $this->getUserProfileData($order)
            )
        );

        echo $this->get_template('widget.html', array(
            'iframe' => $widget->getHtmlCode(array(
                'width' => '100%',
                'height' => '400px'
            )),
            'thankyoutext' => __($this->thankyoutext, PW_JIGO_TEXT_DOMAIN),
        ));

        jigoshop_cart::empty_cart();
    }

    /**
     * Process pingback request
     */
    function check_ipn_response()
    {
        if (isset($_GET['paymentwallListener']) && $_GET['paymentwallListener'] == 'paymentwall_IPN') {
            $this->init_paymentwall_configs();
            $pingback = new Paymentwall_Pingback($_GET, $_SERVER['REMOTE_ADDR']);

            if ($pingback->validate()) {
                $order = new jigoshop_order($pingback->getProductId());

                // Check order exist and payment method is paymentwall
                if ($order->id && $order->payment_method == $this->id) {
                    $order->add_order_note(__('IPN payment completed', PW_JIGO_TEXT_DOMAIN));

                    if ($pingback->isDeliverable()) {

                        // Call Delivery Confirmation API
                        if ($this->delivery == 'yes') {
                            // Delivery Confirmation
                            $delivery = new Paymentwall_GenerericApiObject('delivery');
                            $response = $delivery->post($this->prepare_delivery_confirmation_data($order, $pingback->getReferenceId()));
                        }

                        $order->update_status('processing', __('Order approved! Transaction ID: #' . $pingback->getReferenceId(), PW_JIGO_TEXT_DOMAIN));
                        $order->payment_complete();
                    } elseif ($pingback->isCancelable()) {
                        $order->update_status(PW_JIGO_ORDER_STATUS_CANCELED, __('Order canceled by Paymentwall!', PW_JIGO_TEXT_DOMAIN));
                    }

                    echo PW_JIGO_DEFAULT_SUCCESS_PINGBACK_VALUE; // Paymentwall expects response to be OK, otherwise the pingback will be resent
                } else {
                    echo __("Undefined order or Payment method is invalid!", PW_JIGO_TEXT_DOMAIN);
                }
            } else {
                echo $pingback->getErrorSummary();
            }
            die();
        }
    }

    /**
     * @param $order
     * @param $ref
     * @return array
     */
    function prepare_delivery_confirmation_data($order, $ref)
    {
        return array(
            'payment_id' => $ref,
            'type' => 'digital',
            'status' => 'delivered',
            'estimated_delivery_datetime' => date('Y/m/d H:i:s'),
            'estimated_update_datetime' => date('Y/m/d H:i:s'),
            'refundable' => 'yes',
            'details' => 'Item will be delivered via email by ' . date('Y/m/d H:i:s'),
            'shipping_address[email]' => $order->billing_email,
            'shipping_address[firstname]' => $order->shipping_first_name,
            'shipping_address[lastname]' => $order->shipping_last_name,
            'shipping_address[country]' => $order->shipping_country,
            'shipping_address[street]' => $order->shipping_address_1,
            'shipping_address[state]' => $order->shipping_state,
            'shipping_address[zip]' => $order->shipping_postcode,
            'shipping_address[city]' => $order->shipping_city,
            'reason' => 'none',
        );
    }
}