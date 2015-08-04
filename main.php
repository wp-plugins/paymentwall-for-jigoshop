<?php

/*
 * Plugin Name: Paymentwall for Jigoshop
 * Plugin URI: http://www.paymentwall.com/
 * Description: Paymentwall Gateway for Jigoshop
 * Version: 1.1.3
 * Author: The Paymentwall Team
 * Author URI: http://www.paymentwall.com/
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 */

define('DEFAULT_SUCCESS_PINGBACK_VALUE', 'OK');
define('JS_ORDER_STATUS_COMPLETED', 'completed');
define('JS_ORDER_STATUS_CANCELED', 'canceled');

add_action('plugins_loaded', 'paymentwall_jigoshop_gateway', 10);
function paymentwall_jigoshop_gateway()
{
    if (!class_exists('jigoshop_payment_gateway')) return; // if the Jigoshop payment gateway class is not available, do nothing

    include(dirname(__FILE__) . '/lib/paymentwall-php/lib/paymentwall.php');
    include(dirname(__FILE__) . '/paymentwall_gateway.php');

    // Register payment gateway
    add_filter('jigoshop_payment_gateways', 'add_paymentwall');
    function add_paymentwall($methods)
    {
        $methods[] = 'jigoshop_paymentwall';
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
