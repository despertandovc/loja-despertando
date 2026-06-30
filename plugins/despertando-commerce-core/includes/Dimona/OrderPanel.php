<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

use Despertando\Commerce\Core\IntegrationLogger;

if (!defined('ABSPATH')) {
    exit;
}

final class OrderPanel
{
    private IntegrationLogger $logger;

    public function __construct(IntegrationLogger $logger)
    {
        $this->logger = $logger;
    }

    public function registerHooks(): void
    {
        add_action('add_meta_boxes', [$this, 'registerMetaBox']);
    }

    public function registerMetaBox(): void
    {
        foreach (['shop_order', 'woocommerce_page_wc-orders'] as $screen) {
            add_meta_box(
                'dcc-dimona-order-panel',
                'Dimona — Despertando Core',
                [$this, 'render'],
                $screen,
                'side',
                'default'
            );
        }
    }

    /**
     * @param mixed $postOrOrderObject
     */
    public function render($postOrOrderObject): void
    {
        $order = $this->resolveOrder($postOrOrderObject);
        if (!$order instanceof \WC_Order) {
            echo '<p>Pedido não encontrado.</p>';
            return;
        }

        $orderId = $order->get_id();
        $summary = (string) $order->get_meta('_dcc_fulfillment_summary');
        $routedAt = (string) $order->get_meta('_dcc_fulfillment_routed_at');
        $dimonaStatus = (string) $order->get_meta('_dcc_dimona_status');
        $dimonaOrderId = (string) $order->get_meta('_dcc_dimona_order_id');
        $url = wp_nonce_url(admin_url('admin-post.php?action=dcc_dimona_reprocess&order_id=' . $orderId), 'dcc_dimona_reprocess_' . $orderId);

        echo '<p><strong>Status:</strong> ' . esc_html($dimonaStatus !== '' ? $dimonaStatus : 'Aguardando integração real') . '</p>';
        echo '<p><strong>Dimona order ID:</strong> ' . esc_html($dimonaOrderId !== '' ? $dimonaOrderId : 'não criado') . '</p>';
        echo '<p><strong>Roteado em:</strong> ' . esc_html($routedAt !== '' ? $routedAt : 'ainda não roteado') . '</p>';
        echo '<p><strong>Resumo:</strong><br><code>' . esc_html($summary !== '' ? $summary : '{}') . '</code></p>';
        echo '<p><a class="button" href="' . esc_url($url) . '">Reprocessar Dimona</a></p>';
        echo '<p class="description">Nesta versão, o reprocessamento registra intenção operacional e log sanitizado. A chamada real da API entra na próxima fase.</p>';
    }

    /**
     * @param mixed $postOrOrderObject
     */
    private function resolveOrder($postOrOrderObject): ?\WC_Order
    {
        if ($postOrOrderObject instanceof \WC_Order) {
            return $postOrOrderObject;
        }

        if (is_object($postOrOrderObject) && isset($postOrOrderObject->ID)) {
            $order = wc_get_order((int) $postOrOrderObject->ID);
            return $order instanceof \WC_Order ? $order : null;
        }

        return null;
    }
}
