<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\WooCommerce;

if (!defined('ABSPATH')) {
    exit;
}

final class CartShippingCalculator
{
    public function registerHooks(): void
    {
        add_filter('woocommerce_shipping_calculator_enable_country', '__return_false');
        add_filter('woocommerce_shipping_calculator_enable_state', '__return_false');
        add_filter('woocommerce_shipping_calculator_enable_city', '__return_false');
        add_filter('woocommerce_shipping_calculator_enable_postcode', '__return_true');
        add_filter('woocommerce_default_address_fields', [$this, 'adjustPostcodeLabel'], 20);
        add_filter('gettext', [$this, 'translateCartShippingCalculatorText'], 20, 3);
        add_action('wp_loaded', [$this, 'forceBrazilForCartShippingCalculator'], 5);
        add_filter('woocommerce_customer_get_shipping_country', [$this, 'defaultBrazilCountry'], 10, 2);
        add_filter('woocommerce_customer_get_billing_country', [$this, 'defaultBrazilCountry'], 10, 2);
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     * @return array<string, array<string, mixed>>
     */
    public function adjustPostcodeLabel(array $fields): array
    {
        if (isset($fields['postcode'])) {
            $fields['postcode']['label'] = 'CEP';
            $fields['postcode']['placeholder'] = 'Digite seu CEP';
            $fields['postcode']['priority'] = 10;
        }

        return $fields;
    }


    public function translateCartShippingCalculatorText(string $translation, string $text, string $domain): string
    {
        if ($domain !== 'woocommerce') {
            return $translation;
        }

        return match ($text) {
            'Calculate shipping' => 'Calcular frete',
            'Postcode / ZIP:' => 'CEP:',
            'Update' => $this->isCartShippingCalculatorRequest() ? 'Calcular' : $translation,
            default => $translation,
        };
    }

    public function forceBrazilForCartShippingCalculator(): void
    {
        if (!function_exists('WC') || !WC()->customer) {
            return;
        }

        if (!$this->isCartShippingCalculatorRequest()) {
            return;
        }

        WC()->customer->set_shipping_country('BR');
        WC()->customer->set_billing_country('BR');

        $postcode = isset($_POST['calc_shipping_postcode']) ? wc_clean(wp_unslash((string) $_POST['calc_shipping_postcode'])) : '';
        if ($postcode !== '') {
            WC()->customer->set_shipping_postcode($postcode);
            WC()->customer->set_billing_postcode($postcode);
        }

        WC()->customer->save();
    }

    /**
     * @param mixed $value
     * @param mixed $customer
     */
    public function defaultBrazilCountry($value, $customer): string
    {
        return is_string($value) && $value !== '' ? $value : 'BR';
    }

    private function isCartShippingCalculatorRequest(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        return isset($_POST['calc_shipping']) || isset($_POST['calc_shipping_postcode']);
    }
}
