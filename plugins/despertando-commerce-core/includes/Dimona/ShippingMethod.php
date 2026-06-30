<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

if (!defined('ABSPATH')) {
    exit;
}

final class ShippingMethod
{
    public static function registerHooks(): void
    {
        add_action('woocommerce_shipping_init', [self::class, 'loadMethodClass']);
        add_filter('woocommerce_shipping_methods', [self::class, 'registerMethod']);
    }

    public static function loadMethodClass(): void
    {
        if (!class_exists(__NAMESPACE__ . '\\WooCommerceShippingMethod')) {
            require_once DCC_PLUGIN_DIR . 'includes/Dimona/WooCommerceShippingMethod.php';
        }
    }

    /**
     * @param array<string, string> $methods
     * @return array<string, string>
     */
    public static function registerMethod(array $methods): array
    {
        self::loadMethodClass();
        $methods['dcc_dimona_shipping'] = __NAMESPACE__ . '\\WooCommerceShippingMethod';
        return $methods;
    }
}
