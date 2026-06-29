<?php
/**
 * Plugin Name: Despertando Commerce Core
 * Description: Núcleo operacional da Loja Despertando para fulfillment, Dimona, logs e futuras integrações de marketplace, afiliados e dropshipping.
 * Version: 0.1.0
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

define('DCC_VERSION', '0.1.0');
define('DCC_PLUGIN_FILE', __FILE__);
define('DCC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DCC_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once DCC_PLUGIN_DIR . 'includes/IntegrationLogger.php';
require_once DCC_PLUGIN_DIR . 'includes/FulfillmentTypes.php';
require_once DCC_PLUGIN_DIR . 'includes/FulfillmentRouter.php';
require_once DCC_PLUGIN_DIR . 'includes/AdminPage.php';
require_once DCC_PLUGIN_DIR . 'includes/Plugin.php';

register_activation_hook(__FILE__, ['Despertando\\Commerce\\Core\\Plugin', 'activate']);

add_action('plugins_loaded', static function (): void {
    Despertando\Commerce\Core\Plugin::instance()->boot();
});
