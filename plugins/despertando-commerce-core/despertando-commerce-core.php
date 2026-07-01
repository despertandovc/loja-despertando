<?php
/**
 * Plugin Name: Despertando Commerce Core
 * Description: Núcleo operacional da Loja Despertando para fulfillment, Dimona, logs e futuras integrações de marketplace, afiliados e dropshipping.
 * Version: 0.3.0
 * Author: Despertando
 * Text Domain: despertando-commerce-core
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * WC requires at least: 8.0
 * WC tested up to: 10.9
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('DCC_VERSION', '0.3.0');
define('DCC_PLUGIN_FILE', __FILE__);
define('DCC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DCC_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once DCC_PLUGIN_DIR . 'includes/IntegrationLogger.php';
require_once DCC_PLUGIN_DIR . 'includes/FulfillmentTypes.php';
require_once DCC_PLUGIN_DIR . 'includes/FulfillmentRouter.php';
require_once DCC_PLUGIN_DIR . 'includes/AdminPage.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/Settings.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/ProductFields.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/OrderPanel.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/ReprocessAction.php';
require_once DCC_PLUGIN_DIR . 'includes/WooCommerce/CartShippingCalculator.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/HttpClient.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/ShippingMethod.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/WebhookController.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/TrackingRepository.php';
require_once DCC_PLUGIN_DIR . 'includes/Dimona/OrderService.php';
require_once DCC_PLUGIN_DIR . 'includes/Plugin.php';


add_action('before_woocommerce_init', static function (): void {
    if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

register_activation_hook(__FILE__, ['Despertando\\Commerce\\Core\\Plugin', 'activate']);

add_action('plugins_loaded', static function (): void {
    Despertando\Commerce\Core\Plugin::instance()->boot();
});
