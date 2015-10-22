<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for Paymentwall
 */
return array(
    array(
        'name' => sprintf(__('%s', PW_JIGO_TEXT_DOMAIN), '<img style="vertical-align:middle;margin-left:10px;" src="https://www.paymentwall.com/uploaded/files/pw-logo-light.png" alt="Paymentwall">'),
        'type' => 'title',
        'desc' => __('This plugin extends the Jigoshop payment gateways by adding a Paymentwall payment solution.<br/>Visit <a href="https://api.paymentwall.com/pwaccount/signup?source=jigoshop&mode=merchant">http://www.paymentwall.com</a> for more information and to register a merchant', PW_JIGO_TEXT_DOMAIN)
    ),
    array(
        'name' => __('Enable Paymentwall solution', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => '',
        'id' => 'jigoshop_paymentwall_enabled',
        'std' => 'yes',
        'type' => 'checkbox',
        'choices' => array(
            'no' => __('No', PW_JIGO_TEXT_DOMAIN),
            'yes' => __('Yes', PW_JIGO_TEXT_DOMAIN)
        )
    ),
    array(
        'name' => __('Method title', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => __('This controls the title which the user sees during checkout.', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_paymentwall_title',
        'std' => __('Paymentwall', PW_JIGO_TEXT_DOMAIN),
        'type' => 'longtext'
    ),
    array(
        'name' => __('Description', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => __('This controls the description which the user sees during checkout.', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_paymentwall_description',
        'std' => __("Pay via Paymentwall.", PW_JIGO_TEXT_DOMAIN),
        'type' => 'longtext'
    ),
    array(
        'name' => __('Pay page text', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => __('Text on iframe text widget.', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_paymentwall_thankyoutext',
        'std' => __("Please complete payment for your order with the widget bellow.", PW_JIGO_TEXT_DOMAIN),
        'type' => 'longtext'
    ),
    array(
        'name' => __('Project key', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => __('Please enter your the Project Key; this is needed in order to make payment!', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_paymentwall_app_key',
        'std' => '',
        'type' => 'longtext'
    ),
    array(
        'name' => __('Secret key', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => __('Please enter your Secret Key; this is needed in order to make payment!', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_paymentwall_secret_key',
        'std' => '',
        'type' => 'longtext'
    ),
    array(
        'name' => __('Widget code', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => __('e.g. p1_1, p4_1', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_paymentwall_widget',
        'std' => __('p1_1', PW_JIGO_TEXT_DOMAIN),
        'type' => 'longtext'
    ),
    array(
        'name' => __('Success url', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => __('URL after success payment', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_paymentwall_successurl',
        'std' => __('', PW_JIGO_TEXT_DOMAIN),
        'type' => 'longtext'
    ),
    array(
        'name' => __('Enable Delivery Confirmation API', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => '',
        'id' => 'jigoshop_paymentwall_delivery',
        'std' => 'no',
        'type' => 'checkbox',
        'choices' => array(
            'no' => __('No', PW_JIGO_TEXT_DOMAIN),
            'yes' => __('Yes', PW_JIGO_TEXT_DOMAIN)
        )
    ),
    array(
        'name' => __('Enable test mode', PW_JIGO_TEXT_DOMAIN),
        'desc' => 'Enable to make test transactions.',
        'tip' => '',
        'id' => 'jigoshop_paymentwall_testmode',
        'std' => 'no',
        'type' => 'checkbox',
        'choices' => array(
            'no' => __('No', PW_JIGO_TEXT_DOMAIN),
            'yes' => __('Yes', PW_JIGO_TEXT_DOMAIN)
        )
    )
);