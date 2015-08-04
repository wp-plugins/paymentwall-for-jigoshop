<?php

/*
 * Paymentwall Gateway for Jigoshop
 *
 * Description: Official Paymentwall module for WordPress WooCommerce.
 * Plugin URI: https://www.paymentwall.com/en/documentation/Jigoshop-WordPress/1168
 * Version: 1.1.3
 * Author: Paymentwall
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 *
 */

class jigoshop_paymentwall extends jigoshop_payment_gateway
{

    public function __construct()
    {
        parent::__construct();

        $this->id = 'paymentwall';
        $this->has_fields = false;
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->icon = plugins_url('images/icon.png', __FILE__);

        $options = Jigoshop_Base::get_options();

        // Prepare configs
        $this->enabled = $options->get('jigoshop_paymentwall_enabled');
        $this->title = $options->get('jigoshop_paymentwall_title');
        $this->appkey = $options->get('jigoshop_paymentwall_app_key');
        $this->secretkey = $options->get('jigoshop_paymentwall_secret_key');
        $this->widget = $options->get('jigoshop_paymentwall_widget');
        $this->description = $options->get('jigoshop_paymentwall_description');
        $this->thankyoutext = $options->get('jigoshop_paymentwall_thankyoutext');
        $this->successurl = $options->get('jigoshop_paymentwall_successurl');
        $this->testmode = $options->get('jigoshop_paymentwall_testmode');
        $this->currency = $options->get('jigoshop_currency');

        $this->notify_url = jigoshop_request_api::query_request('?js-api=Paymentwall_Gateway', false);

        add_action('init', array($this, 'check_ipn_response'));
        add_action('receipt_' . $this->id, array($this, 'receipt_paymentwall'));
    }

    public function paymentwall_init()
    {
        Paymentwall_Config::getInstance()->set(array(
            'api_type' => Paymentwall_Config::API_GOODS,
            'public_key' => $this->appkey, // available in your Paymentwall merchant area
            'private_key' => $this->secretkey // available in your Paymentwall merchant area
        ));
    }

    function receipt_paymentwall($order_id)
    {
        $this->paymentwall_init();
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
                    'test_mode' => (int)$this->testmode,
                    'integration_module' => 'jigoshop'
                ),
                $this->getUserProfileData($order)
            )
        );

        echo $this->getTemplate('widget.html', array(
            'iframe' => $widget->getHtmlCode(array(
                'width' => '100%',
                'height' => '400px'
            )),
            'thankyoutext' => __($this->thankyoutext, 'jigoshop'),
        ));

        jigoshop_cart::empty_cart();
    }

    /**
     * Retrieve user data for User Profile API
     * @param $order
     * @return array
     */
    private function getUserProfileData($order){
        return array(
            'customer[city]' => $order->billing_city,
            'customer[state]' => $order->billing_state,
            'customer[address]' => $order->billing_address_1,
            'customer[country]' => $order->billing_country,
            'customer[zip]' => $order->billing_postcode,
            'customer[username]' => ($order->user_id == 0) ? $order->billing_email : $order->user_id,
            'customer[firstname]' => $order->billing_first_name,
            'customer[lastname]' => $order->billing_last_name,
            'email' => $order->billing_email,
        );
    }

    function process_payment($order_id)
    {
        $order = new jigoshop_order($order_id);

        return array(
            'result' => 'success',
            'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(jigoshop_get_page_id('pay'))))
        );

    }

    function payment_fields()
    {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
    }

    /**
     * Process pingback request
     */
    function check_ipn_response()
    {
        if (isset($_GET['paymentwallListener']) && $_GET['paymentwallListener'] == 'paymentwall_IPN') {

            $this->paymentwall_init();
            unset($_GET['paymentwallListener']);
            $pingback = new Paymentwall_Pingback($_GET, $_SERVER['REMOTE_ADDR']);

            if ($pingback->validate()) {

                $productId = $pingback->getProduct()->getId();
                $order = new jigoshop_order((int)$productId);

                if ($order->id) {
                    if ($pingback->isDeliverable()) {
                        $order->update_status(JS_ORDER_STATUS_COMPLETED, __('Order completed!', 'jigoshop'));
                    } elseif ($pingback->isCancelable()) {
                        $order->update_status(JS_ORDER_STATUS_CANCELED, __('Order canceled by Paymentwall!', 'jigoshop'));
                    }

                    $order->add_order_note(__('IPN payment completed', 'jigoshop'));
                    echo DEFAULT_SUCCESS_PINGBACK_VALUE; // Paymentwall expects response to be OK, otherwise the pingback will be resent
                } else {
                    echo __("Undefined order!");
                }
            } else {
                echo $pingback->getErrorSummary();
            }

            die();
        }
    }

    function getTemplate($templateFileName, $data)
    {
        if (file_exists($this->plugin_path . 'templates/' . $templateFileName)) {
            $content = file_get_contents($this->plugin_path . 'templates/' . $templateFileName);
            foreach ($data as $key => $var) {
                $content = str_replace('{{' . $key . '}}', $var, $content);
            }
            return $content;
        }
        return false;
    }

    protected function get_default_options()
    {
        return array(
            array(
                'name' => sprintf(__('Paymentwall %s', 'jigoshop'), '<img style="vertical-align:middle;margin-left:10px;" src="' . plugins_url('images/icon.png', __FILE__) . '" alt="Paymentwall">'),
                'type' => 'title',
                'desc' => __('This plugin extends the Jigoshop payment gateways by adding a Paymentwall payment solution.', 'jigoshop')
            ),
            array(
                'name' => __('Enable Paymentwall solution', 'jigoshop'),
                'desc' => '',
                'tip' => '',
                'id' => 'jigoshop_paymentwall_enabled',
                'std' => 'yes',
                'type' => 'checkbox',
                'choices' => array(
                    'no' => __('No', 'jigoshop'),
                    'yes' => __('Yes', 'jigoshop')
                )
            ),
            array(
                'name' => __('Method title', 'jigoshop'),
                'desc' => '',
                'tip' => __('This controls the title which the user sees during checkout.', 'jigoshop'),
                'id' => 'jigoshop_paymentwall_title',
                'std' => __('Paymentwall', 'jigoshop'),
                'type' => 'text'
            ),
            array(
                'name' => __('Description', 'jigoshop'),
                'desc' => '',
                'tip' => __('This controls the description which the user sees during checkout.', 'jigoshop'),
                'id' => 'jigoshop_paymentwall_description',
                'std' => __("Pay via Paymentwall.", 'jigoshop'),
                'type' => 'longtext'
            ),
            array(
                'name' => __('Thank you page text', 'jigoshop'),
                'desc' => '',
                'tip' => __('Text on iframe text widget.', 'jigoshop'),
                'id' => 'jigoshop_paymentwall_thankyoutext',
                'std' => __("Thank you for purchase!", 'jigoshop'),
                'type' => 'longtext'
            ),
            array(
                'name' => __('Project key', 'jigoshop'),
                'desc' => '',
                'tip' => __('Please enter your the Project Key; this is needed in order to make payment!', 'jigoshop'),
                'id' => 'jigoshop_paymentwall_app_key',
                'std' => '',
                'type' => 'text'
            ),
            array(
                'name' => __('Secret key', 'jigoshop'),
                'desc' => '',
                'tip' => __('Please enter your Secret Key; this is needed in order to make payment!', 'jigoshop'),
                'id' => 'jigoshop_paymentwall_secret_key',
                'std' => '',
                'type' => 'text'
            ),
            array(
                'name' => __('Widget code', 'jigoshop'),
                'desc' => '',
                'tip' => __('e.g. p1_1, p4_1', 'jigoshop'),
                'id' => 'jigoshop_paymentwall_widget',
                'std' => __('p1_1', 'jigoshop'),
                'type' => 'text'
            ),
            array(
                'name' => __('Success url', 'jigoshop'),
                'desc' => '',
                'tip' => __('URL after success payment', 'jigoshop'),
                'id' => 'jigoshop_paymentwall_successurl',
                'std' => __('', 'jigoshop'),
                'type' => 'text'
            ),
            array(
                'name' => __('Test mode?', 'jigoshop'),
                'desc' => '',
                'tip' => '',
                'id' => 'jigoshop_paymentwall_testmode',
                'std' => 'no',
                'type' => 'checkbox',
                'choices' => array(
                    'no' => __('No', 'jigoshop'),
                    'yes' => __('Yes', 'jigoshop')
                )
            )
        );
    }
}