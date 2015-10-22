<?php

/**
 * Paymentwall Gateway for Jigoshop
 *
 * Description: Official Paymentwall module for WordPress WooCommerce.
 * Plugin URI: https://www.paymentwall.com/en/documentation/Jigoshop-WordPress/1168
 * Author: Paymentwall
 */
class jigoshop_brick extends paymentwall_gateway
{
    public $id = 'brick';
    public $has_fields = false;

    public function __construct()
    {
        parent::__construct();

        $options = Jigoshop_Base::get_options();

        // Prepare configs
        $this->icon = PW_JIGO_PLUGIN_URL . '/assets/images/brick_logo.png';
        $this->enabled = $options->get('jigoshop_brick_enabled');
        $this->title = $options->get('jigoshop_brick_title');
        $this->public_key = $options->get('jigoshop_brick_public_key');
        $this->private_key = $options->get('jigoshop_brick_private_key');
        $this->public_test_key = $options->get('jigoshop_brick_public_test_key');
        $this->private_test_key = $options->get('jigoshop_brick_private_test_key');
        $this->description = $options->get('jigoshop_brick_description');
        $this->testmode = $options->get('jigoshop_brick_testmode');
        $this->currency = $options->get('jigoshop_currency');

        //$this->notify_url = jigoshop_request_api::query_request('?js-api=jigoshop_brick', false);

        add_action('receipt_' . $this->id, array($this, 'receipt_page'));
    }

    function init_paymentwall_configs()
    {
        Paymentwall_Config::getInstance()->set(array(
            'api_type' => Paymentwall_Config::API_GOODS,
            'public_key' => $this->testmode == 'yes' ? $this->public_test_key : $this->public_key, // Available in your Paymentwall merchant area
            'private_key' => $this->testmode == 'yes' ? $this->private_test_key : $this->private_key // Available in your Paymentwall merchant area
        ));
    }

    /**
     * Displays credit card form
     */
    public function payment_fields()
    {
        $this->init_paymentwall_configs();
        echo $this->get_template('cc_form.html', array(
            'payment_id' => $this->id,
            'public_key' => Paymentwall_Config::getInstance()->getPublicKey(),
            'entry_card_number' => __("Card number", PW_JIGO_TEXT_DOMAIN),
            'entry_card_expiration' => __("Card expiration", PW_JIGO_TEXT_DOMAIN),
            'entry_card_cvv' => __("Card CVV", PW_JIGO_TEXT_DOMAIN),
        ));
    }

    /**
     * @param int $order_id
     * @return array
     */
    function process_payment($order_id)
    {
        $order = new jigoshop_order($order_id);
        $this->init_paymentwall_configs();
        $return = array(
            'result' => 'fail',
            'redirect' => ''
        );

        $charge = new Paymentwall_Charge();

        try {
            $charge->create(array_merge(
                $this->prepare_user_profile_data($order), // for User Profile API
                $this->prepare_card_info($order)
            ));

            $response = $charge->getPublicData();

            if ($charge->isSuccessful()) {
                if ($charge->isCaptured()) {
                    // Add order note
                    $order->add_order_note(sprintf(__('Brick payment approved (ID: %s, Card: xxxx-%s)', PW_JIGO_TEXT_DOMAIN), $charge->getId(), $charge->getCard()->getAlias()));

                    // Payment complete
                    $order->payment_complete();

                    $return['result'] = 'success';

                    $checkout_redirect = apply_filters('jigoshop_get_checkout_redirect_page_id', jigoshop_get_page_id('thanks'));
                    $return['redirect'] = add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink($checkout_redirect)));
                } elseif ($charge->isUnderReview()) {
                    $order->update_status('on-hold');
                }

                // Clear shopping cart
                jigoshop_cart::empty_cart();
            } else {
                $errors = json_decode($response, true);
                jigoshop::add_error(__($errors['error']['message']), 'error');
            }

        } catch (Exception $e) {
            jigoshop::add_error($e->getMessage(), 'error');
        }

        // Return redirect
        return $return;
    }

    /**
     * @param $order
     * @return array
     * @throws Exception
     */
    function prepare_card_info($order)
    {
        if (!isset($_POST['brick'])) {
            throw new Exception("Payment Invalid!");
        }

        $brick = $_POST['brick'];
        return array(
            'token' => $brick['token'],
            'amount' => $order->order_total,
            'currency' => $this->currency,
            'email' => $order->billing_email,
            'fingerprint' => $brick['fingerprint'],
            'description' => sprintf(__('%s - Order #%s', PW_JIGO_TEXT_DOMAIN), esc_html(get_bloginfo('name', 'display')), $order->get_order_number()),
        );
    }
}