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
        add_action('woocommerce_before_shipping_calculator', [$this, 'renderBrazilHiddenFields'], 5);
        add_action('wp_loaded', [$this, 'forceBrazilForCartShippingCalculator'], 1);
        add_action('wp_footer', [$this, 'renderCartShippingScript'], 20);
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
            'Change address' => 'Trocar CEP',
            'Enter a different address' => 'Trocar CEP',
            'Postcode / ZIP:' => 'CEP:',
            'Update' => $this->isCartShippingCalculatorRequest() ? 'Calcular' : $translation,
            default => $translation,
        };
    }

    public function renderBrazilHiddenFields(): void
    {
        echo '<input type="hidden" name="calc_shipping_country" value="BR">' . "\n";
    }


    public function renderCartShippingScript(): void
    {
        if (!function_exists('is_cart') || !is_cart()) {
            return;
        }
        ?>
        <script>
        (function () {
            function ensureBrazilHiddenField() {
                var forms = document.querySelectorAll('form.woocommerce-shipping-calculator');
                forms.forEach(function (form) {
                    var field = form.querySelector('input[name="calc_shipping_country"]');
                    if (!field) {
                        field = document.createElement('input');
                        field.type = 'hidden';
                        field.name = 'calc_shipping_country';
                        form.appendChild(field);
                    }
                    field.value = 'BR';
                });
            }

            document.addEventListener('DOMContentLoaded', ensureBrazilHiddenField);
            document.addEventListener('submit', function (event) {
                if (event.target && event.target.matches('form.woocommerce-shipping-calculator')) {
                    ensureBrazilHiddenField();
                }
            }, true);
            document.addEventListener('click', function (event) {
                if (event.target && event.target.closest('.shipping-calculator-button')) {
                    window.setTimeout(ensureBrazilHiddenField, 50);
                }
            }, true);
            document.body && document.body.addEventListener('updated_wc_div', ensureBrazilHiddenField);
            ensureBrazilHiddenField();
        })();
        </script>
        <?php
    }

    public function forceBrazilForCartShippingCalculator(): void
    {
        if (!function_exists('WC') || !WC()->customer) {
            return;
        }

        if (!$this->isCartShippingCalculatorRequest()) {
            return;
        }

        $_POST['calc_shipping_country'] = 'BR';

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
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }

        return isset($_POST['calc_shipping']) || isset($_POST['calc_shipping_postcode']);
    }
}
