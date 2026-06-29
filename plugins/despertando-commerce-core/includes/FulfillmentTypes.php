<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core;

if (!defined('ABSPATH')) {
    exit;
}

final class FulfillmentTypes
{
    public const META_KEY = '_dcc_fulfillment_type';

    public function registerHooks(): void
    {
        add_action('woocommerce_product_options_general_product_data', [$this, 'renderProductField']);
        add_action('woocommerce_admin_process_product_object', [$this, 'saveProductField']);
    }

    /**
     * @return array<string, array{label: string, enabled: bool, description: string}>
     */
    public function all(): array
    {
        return [
            'own_stock' => [
                'label' => 'Estoque próprio',
                'enabled' => true,
                'description' => 'Produto físico próprio com envio operado pela Loja Despertando.',
            ],
            'dimona' => [
                'label' => 'Dimona API',
                'enabled' => true,
                'description' => 'Produto print on demand roteado para integração Dimona via API.',
            ],
            'marketplace' => [
                'label' => 'Marketplace',
                'enabled' => false,
                'description' => 'Produto de vendedor externo, planejado para o MVP 2.',
            ],
            'affiliate' => [
                'label' => 'Afiliado',
                'enabled' => false,
                'description' => 'Produto externo/afiliado, planejado para o MVP 2.',
            ],
            'dropshipping' => [
                'label' => 'Dropshipping',
                'enabled' => false,
                'description' => 'Produto enviado por fornecedor terceiro, planejado para o MVP 2.',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];
        foreach ($this->all() as $key => $definition) {
            $suffix = $definition['enabled'] ? '' : ' — MVP 2';
            $options[$key] = $definition['label'] . $suffix;
        }

        return $options;
    }

    public function renderProductField(): void
    {
        if (!function_exists('woocommerce_wp_select')) {
            return;
        }

        woocommerce_wp_select([
            'id' => self::META_KEY,
            'label' => __('Fulfillment Despertando', 'despertando-commerce-core'),
            'description' => __('Define como este produto será roteado operacionalmente.', 'despertando-commerce-core'),
            'desc_tip' => true,
            'options' => $this->options(),
        ]);
    }

    public function saveProductField(\WC_Product $product): void
    {
        $rawValue = isset($_POST[self::META_KEY]) ? sanitize_key((string) wp_unslash($_POST[self::META_KEY])) : 'own_stock';
        $allowed = array_keys($this->all());
        $value = in_array($rawValue, $allowed, true) ? $rawValue : 'own_stock';

        $product->update_meta_data(self::META_KEY, $value);
    }

    public function productType(int $productId): string
    {
        $value = get_post_meta($productId, self::META_KEY, true);
        $value = is_string($value) && $value !== '' ? sanitize_key($value) : 'own_stock';

        return array_key_exists($value, $this->all()) ? $value : 'own_stock';
    }
}
