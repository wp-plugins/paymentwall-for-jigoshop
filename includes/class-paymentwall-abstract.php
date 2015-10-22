<?php

/**
 * Paymentwall Gateway for Jigoshop
 *
 * Description: Official Paymentwall module for WordPress WooCommerce.
 * Plugin URI: https://www.paymentwall.com/en/documentation/Jigoshop-WordPress/1168
 * Author: Paymentwall
 */
abstract class paymentwall_gateway extends jigoshop_payment_gateway
{

    public function __construct()
    {
        parent::__construct();
    }

    abstract function init_paymentwall_configs();

    /**
     * Retrieve user data for User Profile API
     * @param $order
     * @return array
     */
    protected function getUserProfileData($order)
    {
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

    /**
     * @param int $order_id
     * @return array
     */
    function process_payment($order_id)
    {
        $order = new jigoshop_order($order_id);
        return array(
            'result' => 'success',
            'redirect' => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(jigoshop_get_page_id('pay'))))
        );
    }

    /**
     * @param $subtotal
     * @param $shipping_total
     * @param int $discount
     * @return bool
     */
    public function process_gateway($subtotal, $shipping_total, $discount = 0)
    {
        if (!(isset($subtotal) && isset($shipping_total))) {
            return false;
        }

        if (($subtotal <= 0 && $shipping_total <= 0) || (($subtotal + $shipping_total) - $discount) == 0) {
            return $this->force_payment === 'yes';
        } else if (($subtotal + $shipping_total) - $discount < 0) {
            return false;
        }

        return true;
    }

    function payment_fields()
    {
        if ($this->description) {
            echo wpautop(wptexturize($this->description));
        }
    }

    /**
     * @param $templateFileName
     * @param $data
     * @return bool|mixed|string
     */
    protected function get_template($templateFileName, $data)
    {
        if (file_exists(PW_JIGO_PLUGIN_PATH . 'templates/' . $templateFileName)) {
            $content = file_get_contents(PW_JIGO_PLUGIN_PATH . 'templates/' . $templateFileName);
            foreach ($data as $key => $var) {
                $content = str_replace('{{' . $key . '}}', $var, $content);
            }
            return $content;
        }
        return false;
    }

    /**
     * @return mixed
     */
    protected function get_default_options()
    {
        return include(PW_JIGO_PLUGIN_PATH . 'includes/options/' . $this->id . '.php');
    }

    /**
     * @param $order
     * @return array
     */
    protected function prepare_user_profile_data($order)
    {
        return array(
            'customer[city]' => $order->billing_city,
            'customer[state]' => $order->billing_state,
            'customer[address]' => $order->billing_address_1,
            'customer[country]' => $order->billing_country,
            'customer[zip]' => $order->billing_postcode,
            'customer[username]' => $order->billing_email,
            'customer[firstname]' => $order->billing_first_name,
            'customer[lastname]' => $order->billing_last_name,
            'email' => $order->billing_email,
        );
    }


}