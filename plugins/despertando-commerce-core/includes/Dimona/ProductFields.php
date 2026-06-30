<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

use Despertando\Commerce\Core\FulfillmentTypes;

if (!defined('ABSPATH')) {
    exit;
}

final class ProductFields
{
    public const META_PRODUCT_ID = '_dcc_dimona_product_id';
    public const META_VARIANT_ID = '_dcc_dimona_variant_id';
    public const META_ARTWORK_URL = '_dcc_dimona_artwork_url';
    public const META_MOCKUP_URL = '_dcc_dimona_mockup_url';
    public const META_PRINT_AREA = '_dcc_dimona_print_area';

    public function registerHooks(): void
    {
        add_action('woocommerce_product_options_general_product_data', [$this, 'renderFields']);
        add_action('woocommerce_admin_process_product_object', [$this, 'saveFields']);
    }

    public function renderFields(): void
    {
        if (!function_exists('woocommerce_wp_text_input')) {
            return;
        }

        echo '<div class="options_group dcc-dimona-fields">';
        echo '<p class="form-field"><strong>Dimona API — MVP 1</strong><br><span class="description">Preencha estes campos para produtos com fulfillment <code>dimona</code>. Nenhuma chamada real à API é feita nesta versão.</span></p>';

        woocommerce_wp_text_input([
            'id' => self::META_PRODUCT_ID,
            'label' => __('Dimona product ID', 'despertando-commerce-core'),
            'desc_tip' => true,
            'description' => __('Identificador do produto na Dimona.', 'despertando-commerce-core'),
        ]);

        woocommerce_wp_text_input([
            'id' => self::META_VARIANT_ID,
            'label' => __('Dimona variant ID', 'despertando-commerce-core'),
            'desc_tip' => true,
            'description' => __('Identificador da variação Dimona.', 'despertando-commerce-core'),
        ]);

        woocommerce_wp_text_input([
            'id' => self::META_PRINT_AREA,
            'label' => __('Área de impressão', 'despertando-commerce-core'),
            'placeholder' => 'front',
        ]);

        woocommerce_wp_text_input([
            'id' => self::META_ARTWORK_URL,
            'label' => __('URL da arte', 'despertando-commerce-core'),
            'type' => 'url',
        ]);

        woocommerce_wp_text_input([
            'id' => self::META_MOCKUP_URL,
            'label' => __('URL do mockup', 'despertando-commerce-core'),
            'type' => 'url',
        ]);

        echo '</div>';
    }

    public function saveFields(\WC_Product $product): void
    {
        $textFields = [self::META_PRODUCT_ID, self::META_VARIANT_ID, self::META_PRINT_AREA];
        foreach ($textFields as $field) {
            $value = isset($_POST[$field]) ? sanitize_text_field((string) wp_unslash($_POST[$field])) : '';
            $product->update_meta_data($field, $value);
        }

        foreach ([self::META_ARTWORK_URL, self::META_MOCKUP_URL] as $field) {
            $value = isset($_POST[$field]) ? esc_url_raw((string) wp_unslash($_POST[$field])) : '';
            $product->update_meta_data($field, $value);
        }
    }

    /**
     * @return array<string, string>
     */
    public static function readForProduct(\WC_Product $product): array
    {
        return [
            'fulfillment_type' => (string) $product->get_meta(FulfillmentTypes::META_KEY),
            'dimona_product_id' => (string) $product->get_meta(self::META_PRODUCT_ID),
            'dimona_variant_id' => (string) $product->get_meta(self::META_VARIANT_ID),
            'dimona_print_area' => (string) $product->get_meta(self::META_PRINT_AREA),
            'dimona_artwork_url' => (string) $product->get_meta(self::META_ARTWORK_URL),
            'dimona_mockup_url' => (string) $product->get_meta(self::META_MOCKUP_URL),
        ];
    }
}
