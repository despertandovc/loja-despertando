<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

use Despertando\Commerce\Core\IntegrationLogger;

if (!defined('ABSPATH')) {
    exit;
}

final class WebhookController
{
    private Settings $settings;
    private IntegrationLogger $logger;
    private TrackingRepository $tracking;

    public function __construct(Settings $settings, IntegrationLogger $logger, TrackingRepository $tracking)
    {
        $this->settings = $settings;
        $this->logger = $logger;
        $this->tracking = $tracking;
    }

    public function registerHooks(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route('despertando-commerce/v1', '/dimona/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'handle'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        $payload = $request->get_json_params();
        if (!is_array($payload) || $payload === []) {
            $payload = $request->get_body_params();
        }
        if (!is_array($payload)) {
            return new \WP_REST_Response(['ok' => false, 'error' => 'invalid_payload'], 400);
        }

        $apiKey = isset($payload['api_key']) ? (string) $payload['api_key'] : '';
        if ($this->settings->apiKey() === '' || !hash_equals($this->settings->apiKey(), $apiKey)) {
            $this->logger->log('dimona_webhook_rejected', 'Webhook Dimona rejeitado por api_key inválida.', ['payload' => $this->redact($payload)], null, 'dimona_webhook', 'warning');
            return new \WP_REST_Response(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $dimonaId = isset($payload['dimona_id']) ? sanitize_text_field((string) $payload['dimona_id']) : '';
        if ($dimonaId === '') {
            return new \WP_REST_Response(['ok' => false, 'error' => 'missing_dimona_id'], 400);
        }

        $order = $this->findOrderByDimonaId($dimonaId);
        $orderId = $order instanceof \WC_Order ? $order->get_id() : null;
        $statusName = isset($payload['name']) ? sanitize_text_field((string) $payload['name']) : '';
        $trackingUrl = isset($payload['tracking_url']) ? esc_url_raw((string) $payload['tracking_url']) : '';

        if ($order instanceof \WC_Order) {
            $order->update_meta_data('_dcc_dimona_status', $this->normalizeStatus($statusName));
            $order->update_meta_data('_dcc_dimona_last_status_label', $statusName);
            $order->update_meta_data('_dcc_dimona_last_tracking_at', current_time('mysql'));
            if ($trackingUrl !== '') {
                $order->update_meta_data('_dcc_dimona_tracking_url', $trackingUrl);
            }
            $order->add_order_note('Dimona: status atualizado via webhook para ' . ($statusName !== '' ? $statusName : 'sem nome') . '.');
            $order->save();
        }

        $this->tracking->record([
            'order_id' => $orderId,
            'dimona_order_id' => $dimonaId,
            'tracking_url' => $trackingUrl,
            'status' => $this->normalizeStatus($statusName),
            'status_label' => $statusName,
            'payload' => $this->redact($payload),
        ] + $this->redact($payload));

        $this->logger->log('dimona_webhook_received', 'Webhook Dimona recebido.', [
            'order_id' => $orderId,
            'dimona_id' => $dimonaId,
            'status_id' => $payload['status_id'] ?? null,
            'name' => $statusName,
            'tracking_url' => $trackingUrl,
        ], $orderId, 'dimona_webhook', 'info');

        return new \WP_REST_Response(['ok' => true, 'order_id' => $orderId], 200);
    }

    private function findOrderByDimonaId(string $dimonaId): ?\WC_Order
    {
        $orders = wc_get_orders([
            'limit' => 1,
            'type' => 'shop_order',
            'meta_key' => '_dcc_dimona_order_id',
            'meta_value' => $dimonaId,
            'return' => 'objects',
        ]);
        return isset($orders[0]) && $orders[0] instanceof \WC_Order ? $orders[0] : null;
    }

    private function normalizeStatus(string $statusName): string
    {
        $statusName = mb_strtolower(remove_accents($statusName));
        if (str_contains($statusName, 'entreg')) {
            return 'delivered';
        }
        if (str_contains($statusName, 'fatur')) {
            return 'invoiced';
        }
        if (str_contains($statusName, 'env') || str_contains($statusName, 'trans')) {
            return 'in_transit';
        }
        if (str_contains($statusName, 'cancel')) {
            return 'cancelled';
        }
        return 'status_update';
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function redact(array $payload): array
    {
        if (isset($payload['api_key'])) {
            $payload['api_key'] = '[REDACTED]';
        }
        return $payload;
    }
}
