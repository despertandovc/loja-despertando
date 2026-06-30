<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

use Despertando\Commerce\Core\FulfillmentTypes;
use Despertando\Commerce\Core\IntegrationLogger;

if (!defined('ABSPATH')) {
    exit;
}

final class WooCommerceShippingMethod extends \WC_Shipping_Method
{
    public function __construct(int $instanceId = 0)
    {
        $this->id = 'dcc_dimona_shipping';
        $this->instance_id = absint($instanceId);
        $this->method_title = 'Dimona';
        $this->method_description = 'Frete calculado pelo endpoint Dimona.';
        $this->supports = ['shipping-zones', 'instance-settings'];
        $this->enabled = 'yes';
        $this->title = 'Entrega Dimona';
        $this->init();
    }

    public function init(): void
    {
        $this->init_form_fields();
        $this->init_settings();
        $this->title = (string) $this->get_option('title', 'Entrega Dimona');
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields(): void
    {
        $this->form_fields = [
            'title' => [
                'title' => 'Título',
                'type' => 'text',
                'default' => 'Entrega Dimona',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $package
     */
    public function calculate_shipping($package = []): void
    {
        $settings = new Settings();
        $logger = new IntegrationLogger();
        $client = new HttpClient($settings);
        $quantity = 0;

        foreach (($package['contents'] ?? []) as $cartItem) {
            $product = $cartItem['data'] ?? null;
            if (!$product instanceof \WC_Product) {
                continue;
            }

            $fulfillmentType = (string) $product->get_meta(FulfillmentTypes::META_KEY);
            if ($fulfillmentType === '') {
                $parentId = $product->get_parent_id();
                if ($parentId > 0) {
                    $parent = wc_get_product($parentId);
                    if ($parent instanceof \WC_Product) {
                        $fulfillmentType = (string) $parent->get_meta(FulfillmentTypes::META_KEY);
                    }
                }
            }

            if ($fulfillmentType === FulfillmentTypes::TYPE_DIMONA) {
                $quantity += max(1, (int) ($cartItem['quantity'] ?? 1));
            }
        }

        if ($quantity <= 0) {
            return;
        }

        $destination = $package['destination'] ?? [];
        $zipcode = (string) ($destination['postcode'] ?? '');
        $zipcode = preg_replace('/\D+/', '', $zipcode) ?: '';
        if ($zipcode === '') {
            return;
        }

        $response = $client->quoteShipping($zipcode, $quantity);
        if (!$response['ok']) {
            $logger->log('dimona_shipping_failed', 'Falha ao cotar frete Dimona.', [
                'zipcode' => $zipcode,
                'quantity' => $quantity,
                'status_code' => $response['status_code'] ?? 0,
                'error' => $response['error'] ?? 'unknown',
            ], null, 'dimona_shipping', 'error');
            return;
        }

        $options = is_array($response['body']) ? $response['body'] : [];
        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }
            $methodId = (string) ($option['delivery_method_id'] ?? '');
            if ($methodId === '') {
                continue;
            }
            $label = (string) ($option['name'] ?? 'Entrega Dimona');
            $days = isset($option['business_days']) ? (int) $option['business_days'] : null;
            if ($days !== null && $days > 0) {
                $label .= ' — ' . $days . ' dias úteis';
            }
            $this->add_rate([
                'id' => $this->id . ':' . $methodId,
                'label' => $label,
                'cost' => (float) ($option['value'] ?? 0),
                'meta_data' => [
                    'delivery_method_id' => $methodId,
                    'business_days' => $days,
                    'dimona_shipping_name' => (string) ($option['name'] ?? ''),
                ],
            ]);
        }
    }
}
