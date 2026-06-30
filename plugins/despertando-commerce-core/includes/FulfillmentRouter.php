<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core;

if (!defined('ABSPATH')) {
    exit;
}

final class FulfillmentRouter
{
    private IntegrationLogger $logger;

    private FulfillmentTypes $types;

    public function __construct(IntegrationLogger $logger)
    {
        $this->logger = $logger;
        $this->types = new FulfillmentTypes();
    }

    public function registerHooks(): void
    {
        add_action('woocommerce_payment_complete', [$this, 'routeOrder'], 20);
        add_action('woocommerce_order_status_processing', [$this, 'routeOrder'], 20);
    }

    public function routeOrder(int $orderId): void
    {
        if (!function_exists('wc_get_order')) {
            return;
        }

        $order = wc_get_order($orderId);
        if (!$order instanceof \WC_Order) {
            return;
        }

        if ($order->get_meta('_dcc_fulfillment_routed_at')) {
            return;
        }

        $summary = [];

        foreach ($order->get_items() as $item) {
            if (!$item instanceof \WC_Order_Item_Product) {
                continue;
            }

            $product = $item->get_product();
            if (!$product instanceof \WC_Product) {
                continue;
            }

            $type = $this->types->productType($product->get_id());
            $summary[$type] = ($summary[$type] ?? 0) + (int) $item->get_quantity();
        }

        if ($summary === []) {
            $summary['own_stock'] = 0;
        }

        $order->update_meta_data('_dcc_fulfillment_summary', wp_json_encode($summary));
        $order->update_meta_data('_dcc_fulfillment_routed_at', current_time('mysql'));
        $order->save();

        $this->logger->log(
            'order_routed',
            'Pedido roteado pelo Despertando Commerce Core.',
            ['summary' => wp_json_encode($summary)],
            $orderId,
            'fulfillment_router'
        );

        if (isset($summary['dimona'])) {
            $order->update_meta_data('_dcc_dimona_status', 'pending_api_integration');
            $order->save();
            $order->add_order_note('Despertando Commerce Core: item Dimona detectado. A chamada real da API Dimona será ativada na fase de integração do MVP 1.');
        }
    }
}
