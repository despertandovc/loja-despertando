<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\WooCommerce;

if (!defined('ABSPATH')) {
    exit;
}

final class WooCommerceTestPaymentGateway extends \WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'dcc_test_payment';
        $this->icon = '';
        $this->has_fields = false;
        $this->method_title = 'DCC Pagamento de Teste';
        $this->method_description = 'Gateway sem cobrança real para staging. Conclui o pedido e deixa a integração Dimona em dry-run.';
        $this->supports = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title = (string) $this->get_option('title', 'Pagamento de teste');
        $this->description = (string) $this->get_option('description', 'Use apenas no staging. Nenhuma cobrança real será feita.');
        $this->enabled = (string) $this->get_option('enabled', 'yes');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled' => [
                'title' => 'Ativar',
                'type' => 'checkbox',
                'label' => 'Ativar pagamento de teste no staging',
                'default' => 'yes',
            ],
            'title' => [
                'title' => 'Título',
                'type' => 'text',
                'default' => 'Pagamento de teste',
            ],
            'description' => [
                'title' => 'Descrição',
                'type' => 'textarea',
                'default' => 'Ambiente de testes: nenhuma cobrança real será feita.',
            ],
        ];
    }

    public function is_available(): bool
    {
        if (!TestPaymentGateway::isAllowed()) {
            return false;
        }

        return parent::is_available();
    }

    /**
     * @return array<string, string>
     */
    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);
        if (!$order instanceof \WC_Order) {
            wc_add_notice('Pedido de teste não encontrado.', 'error');
            return ['result' => 'failure'];
        }

        if (!TestPaymentGateway::isAllowed()) {
            wc_add_notice('Pagamento de teste indisponível fora do modo dry-run seguro.', 'error');
            return ['result' => 'failure'];
        }

        $order->add_order_note('DCC Pagamento de Teste: pedido aprovado sem cobrança real no staging.');
        $order->payment_complete('dcc-test-' . $order_id . '-' . time());

        if ($order->get_status() !== 'processing') {
            $order->update_status('processing', 'DCC Pagamento de Teste: pedido movido para processamento em dry-run.');
        }

        if (function_exists('WC') && WC()->cart) {
            WC()->cart->empty_cart();
        }

        return [
            'result' => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }
}
