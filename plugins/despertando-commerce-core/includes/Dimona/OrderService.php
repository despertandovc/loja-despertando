<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

use Despertando\Commerce\Core\FulfillmentTypes;
use Despertando\Commerce\Core\IntegrationLogger;

if (!defined('ABSPATH')) {
    exit;
}

final class OrderService
{
    private Settings $settings;
    private HttpClient $client;
    private IntegrationLogger $logger;

    public function __construct(Settings $settings, HttpClient $client, IntegrationLogger $logger)
    {
        $this->settings = $settings;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function registerHooks(): void
    {
        add_action('woocommerce_order_status_processing', [$this, 'maybeCreateDimonaOrder'], 20, 1);
        add_action('woocommerce_payment_complete', [$this, 'maybeCreateDimonaOrder'], 20, 1);
    }

    public function maybeCreateDimonaOrder(int $orderId): void
    {
        $order = wc_get_order($orderId);
        if (!$order instanceof \WC_Order) {
            return;
        }

        if ((string) $order->get_meta('_dcc_dimona_order_id') !== '') {
            return;
        }

        $payload = $this->buildPayload($order);
        if ($payload === null) {
            return;
        }

        $hash = hash('sha256', wp_json_encode($payload) ?: serialize($payload));
        $order->update_meta_data('_dcc_dimona_last_payload_hash', $hash);
        $order->update_meta_data('_dcc_dimona_last_attempt_at', current_time('mysql'));
        $order->update_meta_data('_dcc_dimona_attempt_count', ((int) $order->get_meta('_dcc_dimona_attempt_count')) + 1);

        if (!$this->settings->isCreateOrderEnabled() || $this->settings->isDryRun()) {
            $order->update_meta_data('_dcc_dimona_status', 'dry_run_ready');
            $order->update_meta_data('_dcc_dimona_dry_run_payload', wp_json_encode($this->sanitizePayload($payload)));
            $order->add_order_note('Dimona: pedido preparado em dry-run. Nenhuma chamada de criação foi enviada para a API.');
            $order->save();
            $this->logger->log('dimona_create_order_dry_run', 'Payload de pedido Dimona preparado em dry-run.', ['order_id' => $orderId, 'payload_hash' => $hash], $orderId, 'dimona_order', 'notice');
            return;
        }

        $response = $this->client->createOrder($payload);
        if (!$response['ok']) {
            $order->update_meta_data('_dcc_dimona_status', 'failed');
            $order->save();
            $this->logger->log('dimona_create_order_failed', 'Falha ao criar pedido Dimona.', [
                'order_id' => $orderId,
                'status_code' => $response['status_code'] ?? 0,
                'error' => $response['error'] ?? 'unknown',
                'payload_hash' => $hash,
            ], $orderId, 'dimona_order', 'error');
            return;
        }

        $body = is_array($response['body']) ? $response['body'] : [];
        $dimonaOrderId = isset($body['order']) ? sanitize_text_field((string) $body['order']) : '';
        if ($dimonaOrderId !== '') {
            $order->update_meta_data('_dcc_dimona_order_id', $dimonaOrderId);
        }
        $order->update_meta_data('_dcc_dimona_status', 'sent_to_dimona');
        $order->add_order_note('Dimona: pedido enviado para produção. ID: ' . ($dimonaOrderId !== '' ? $dimonaOrderId : 'não informado'));
        $order->save();
        $this->logger->log('dimona_create_order_success', 'Pedido Dimona criado.', ['order_id' => $orderId, 'dimona_order_id' => $dimonaOrderId], $orderId, 'dimona_order', 'info');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildPayload(\WC_Order $order): ?array
    {
        $items = [];
        foreach ($order->get_items() as $itemId => $item) {
            $product = $item->get_product();
            if (!$product instanceof \WC_Product) {
                continue;
            }
            $fulfillmentType = (string) $product->get_meta(FulfillmentTypes::META_KEY);
            if ($fulfillmentType === '' && $product->get_parent_id() > 0) {
                $parent = wc_get_product($product->get_parent_id());
                if ($parent instanceof \WC_Product) {
                    $fulfillmentType = (string) $parent->get_meta(FulfillmentTypes::META_KEY);
                }
            }
            if ($fulfillmentType !== FulfillmentTypes::TYPE_DIMONA) {
                continue;
            }
            $dimonaSkuId = (string) $product->get_meta(ProductFields::META_PRODUCT_ID);
            if ($dimonaSkuId === '') {
                $dimonaSkuId = (string) $product->get_meta('_dcc_dimona_sku_id');
            }
            if ($dimonaSkuId === '') {
                $dimonaSkuId = (string) $product->get_meta(ProductFields::META_VARIANT_ID);
            }
            $designs = $this->readJsonMeta($product, '_dcc_dimona_designs');
            $mocks = $this->readJsonMeta($product, '_dcc_dimona_mocks');
            if ($designs === []) {
                $artwork = (string) $product->get_meta(ProductFields::META_ARTWORK_URL);
                if ($artwork !== '') {
                    $designs['front'] = $artwork;
                }
            }
            if ($mocks === []) {
                $mock = (string) $product->get_meta(ProductFields::META_MOCKUP_URL);
                if ($mock !== '') {
                    $mocks['front'] = $mock;
                }
            }
            $items[] = array_filter([
                'name' => $item->get_name(),
                'sku' => $product->get_sku() ?: 'woo-item-' . $itemId,
                'qty' => max(1, (int) $item->get_quantity()),
                'dimona_sku_id' => $dimonaSkuId,
                'designs' => $designs,
                'mocks' => $mocks,
            ], static fn ($value) => $value !== '' && $value !== [] && $value !== null);
        }

        if ($items === []) {
            return null;
        }

        $deliveryMethodId = $this->deliveryMethodIdFromOrder($order);
        $payload = [
            'shipping_speed' => 'pac',
            'order_id' => 'woo-' . $order->get_id(),
            'customer_name' => trim($order->get_formatted_billing_full_name()) ?: trim($order->get_formatted_shipping_full_name()),
            'customer_document' => (string) $order->get_meta('_billing_cpf'),
            'customer_email' => $order->get_billing_email(),
            'webhook_url' => $this->settings->webhookUrl(),
            'items' => $items,
            'address' => [
                'name' => trim($order->get_formatted_shipping_full_name()) ?: trim($order->get_formatted_billing_full_name()),
                'street' => $order->get_shipping_address_1() ?: $order->get_billing_address_1(),
                'number' => (string) $order->get_meta('_shipping_number') ?: (string) $order->get_meta('_billing_number'),
                'complement' => $order->get_shipping_address_2() ?: $order->get_billing_address_2(),
                'city' => $order->get_shipping_city() ?: $order->get_billing_city(),
                'state' => $order->get_shipping_state() ?: $order->get_billing_state(),
                'zipcode' => preg_replace('/\D+/', '', $order->get_shipping_postcode() ?: $order->get_billing_postcode()),
                'neighborhood' => (string) $order->get_meta('_shipping_neighborhood') ?: (string) $order->get_meta('_billing_neighborhood'),
                'phone' => $order->get_billing_phone(),
                'country' => $order->get_shipping_country() ?: $order->get_billing_country() ?: 'BR',
            ],
        ];

        if ($deliveryMethodId !== '') {
            $payload['delivery_method_id'] = $deliveryMethodId;
        }

        return $payload;
    }

    /**
     * @return array<string, string>
     */
    private function readJsonMeta(\WC_Product $product, string $key): array
    {
        $raw = (string) $product->get_meta($key);
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }
        return array_map('esc_url_raw', array_filter($decoded, 'is_string'));
    }

    private function deliveryMethodIdFromOrder(\WC_Order $order): string
    {
        foreach ($order->get_shipping_methods() as $shippingItem) {
            $methodId = (string) $shippingItem->get_meta('delivery_method_id');
            if ($methodId !== '') {
                return $methodId;
            }
            $instance = (string) $shippingItem->get_method_id();
            if (str_starts_with($instance, 'dcc_dimona_shipping')) {
                $rateId = (string) $shippingItem->get_meta('rate_id');
                if (str_contains($rateId, ':')) {
                    return substr(strrchr($rateId, ':'), 1) ?: '';
                }
            }
        }
        return '';
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        unset($payload['customer_document'], $payload['customer_email']);
        if (isset($payload['address']) && is_array($payload['address'])) {
            unset($payload['address']['phone']);
        }
        return $payload;
    }
}
