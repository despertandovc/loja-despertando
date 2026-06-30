<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

use Despertando\Commerce\Core\IntegrationLogger;

if (!defined('ABSPATH')) {
    exit;
}

final class ReprocessAction
{
    private IntegrationLogger $logger;

    public function __construct(IntegrationLogger $logger)
    {
        $this->logger = $logger;
    }

    public function registerHooks(): void
    {
        add_action('admin_post_dcc_dimona_reprocess', [$this, 'handle']);
    }

    public function handle(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Sem permissão para reprocessar pedido.', 'despertando-commerce-core'));
        }

        $orderId = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        if ($orderId <= 0 || !wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'dcc_dimona_reprocess_' . $orderId)) {
            wp_die(esc_html__('Requisição inválida.', 'despertando-commerce-core'));
        }

        $order = function_exists('wc_get_order') ? wc_get_order($orderId) : null;
        if (!$order instanceof \WC_Order) {
            wp_die(esc_html__('Pedido não encontrado.', 'despertando-commerce-core'));
        }

        $order->update_meta_data('_dcc_dimona_status', 'manual_reprocess_requested');
        $order->save();
        $order->add_order_note('Despertando Commerce Core: reprocessamento Dimona solicitado manualmente. Chamada real da API ainda não está ativa nesta versão.');

        $this->logger->log(
            'dimona_reprocess_requested',
            'Reprocessamento Dimona solicitado manualmente.',
            ['order_id' => $orderId],
            $orderId,
            'dimona',
            'notice'
        );

        wp_safe_redirect(wp_get_referer() ?: admin_url('post.php?post=' . $orderId . '&action=edit'));
        exit;
    }
}
