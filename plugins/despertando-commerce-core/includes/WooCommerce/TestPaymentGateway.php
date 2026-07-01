<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\WooCommerce;

use Despertando\Commerce\Core\Dimona\Settings;

if (!defined('ABSPATH')) {
    exit;
}

final class TestPaymentGateway
{
    public static function registerHooks(): void
    {
        add_action('plugins_loaded', [self::class, 'loadGatewayClass'], 20);
        add_filter('woocommerce_payment_gateways', [self::class, 'registerGateway']);
    }

    public static function loadGatewayClass(): void
    {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        if (class_exists(__NAMESPACE__ . '\\WooCommerceTestPaymentGateway')) {
            return;
        }

        require_once DCC_PLUGIN_DIR . 'includes/WooCommerce/WooCommerceTestPaymentGateway.php';
    }

    /**
     * @param array<int|string, mixed> $gateways
     * @return array<int|string, mixed>
     */
    public static function registerGateway(array $gateways): array
    {
        self::loadGatewayClass();

        if (class_exists(__NAMESPACE__ . '\\WooCommerceTestPaymentGateway')) {
            $gateways[] = __NAMESPACE__ . '\\WooCommerceTestPaymentGateway';
        }

        return $gateways;
    }

    public static function isAllowed(): bool
    {
        $environment = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';
        if ($environment === 'production') {
            return false;
        }

        $settings = new Settings();

        return !$settings->isCreateOrderEnabled() && $settings->isDryRun();
    }
}
