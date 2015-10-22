<?php

/*
 * Plugin Name: Paymentwall for Jigoshop
 * Plugin URI: http://www.paymentwall.com/
 * Description: Official Paymentwall module for WordPress Jigoshop.
 * Version: 1.2.0
 * Author: The Paymentwall Team
 * Author URI: http://www.paymentwall.com/
 * License: The MIT License (MIT)
 */

define('PW_JIGO_DEFAULT_SUCCESS_PINGBACK_VALUE', 'OK');
define('PW_JIGO_ORDER_STATUS_COMPLETED', 'completed');
define('PW_JIGO_ORDER_STATUS_CANCELED', 'canceled');
define('PW_JIGO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PW_JIGO_PLUGIN_URL', plugins_url('', __FILE__));
define('PW_JIGO_TEXT_DOMAIN', 'paymentwall-for-jigoshop');

add_action('plugins_loaded', 'paymentwall_jigoshop_gateway', 10);
function paymentwall_jigoshop_gateway()
{
    if (!class_exists('jigoshop_payment_gateway')) return; // if the Jigoshop payment gateway class is not available, do nothing

    include(dirname(__FILE__) . '/lib/paymentwall-php/lib/paymentwall.php');
    include(dirname(__FILE__) . '/includes/class-paymentwall-abstract.php');
    include(dirname(__FILE__) . '/includes/class-paymentwall-gateway.php');
    include(dirname(__FILE__) . '/includes/class-paymentwall-brick.php');

    // Register payment gateway
    add_filter('jigoshop_payment_gateways', 'add_paymentwall', 1);
    function add_paymentwall($methods)
    {
        $methods[] = 'jigoshop_paymentwall';
        $methods[] = 'jigoshop_brick';
        return $methods;
    }

}

add_action('admin_init', 'child_plugin_has_parent_plugin');
function child_plugin_has_parent_plugin()
{
    if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('jigoshop/jigoshop.php')) {
        add_action('admin_notices', 'child_plugin_notice');

        deactivate_plugins(plugin_basename(__FILE__));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

function child_plugin_notice()
{
    ?>
    <div class="error">
        <p>Sorry, but Paymentwall Plugin requires the Jigoshop plugin to be installed and active.</p>
    </div>
<?php
}
