<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for Brick
 */
return array(
    array(
        'name' => sprintf(__('%s', PW_JIGO_TEXT_DOMAIN), '<img style="vertical-align:middle;margin-left:10px;" src="https://www.paymentwall.com/uploaded/files/pw-logo-light.png" alt="Paymentwall payment gateways">'),
        'type' => 'title',
        'desc' => __('This plugin extends the Jigoshop payment gateways by adding a Paymentwall payment solution.<br/>Visit <a href="https://api.paymentwall.com/pwaccount/signup?source=jigoshop&mode=merchant">http://www.paymentwall.com</a> for more information and to register a merchant', PW_JIGO_TEXT_DOMAIN)
    ),
    array(
        'name' => __('Enable Brick payment', PW_JIGO_TEXT_DOMAIN),
        'desc' => 'Brick is Paymentwall\'s premiere credit card solution available for businesses in 200+ countries.',
        'tip' => '',
        'id' => 'jigoshop_brick_enabled',
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
        'id' => 'jigoshop_brick_title',
        'std' => __('Brick', PW_JIGO_TEXT_DOMAIN),
        'type' => 'longtext'
    ),
    array(
        'name' => __('Description', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => __('This controls the description which the user sees during checkout.', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_brick_description',
        'std' => __("Brick is Paymentwall's premiere credit card solution available for businesses in 200+ countries.", PW_JIGO_TEXT_DOMAIN),
        'type' => 'longtext'
    ),
    array(
        'name' => __('Public key', PW_JIGO_TEXT_DOMAIN),
        'desc' => 'Public API Key - used for building the Frontend credit card form. This can be publicly available to your end-users.',
        'tip' => __('Please enter your the Public Key; this is needed in order to make payment!', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_brick_public_key',
        'std' => '',
        'type' => 'longtext'
    ),
    array(
        'name' => __('Private key', PW_JIGO_TEXT_DOMAIN),
        'desc' => 'Private API Key - used for charging cards from your backend. Keep this private at all times.',
        'tip' => __('Please enter your Private Key; this is needed in order to make payment!', PW_JIGO_TEXT_DOMAIN),
        'id' => 'jigoshop_brick_private_key',
        'std' => '',
        'type' => 'longtext'
    ),
    array(
        'name' => __('Public test key', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => '',
        'id' => 'jigoshop_brick_public_test_key',
        'std' => '',
        'type' => 'longtext'
    ),
    array(
        'name' => __('Private key', PW_JIGO_TEXT_DOMAIN),
        'desc' => '',
        'tip' => '',
        'id' => 'jigoshop_brick_private_test_key',
        'std' => '',
        'type' => 'longtext'
    ),
    array(
        'name' => __('Enable test mode', PW_JIGO_TEXT_DOMAIN),
        'desc' => 'Enable to make test transactions.',
        'tip' => '',
        'id' => 'jigoshop_brick_testmode',
        'std' => 'no',
        'type' => 'checkbox',
        'choices' => array(
            'no' => __('No', PW_JIGO_TEXT_DOMAIN),
            'yes' => __('Yes', PW_JIGO_TEXT_DOMAIN)
        )
    )
);