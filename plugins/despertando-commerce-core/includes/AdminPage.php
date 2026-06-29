<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core;

if (!defined('ABSPATH')) {
    exit;
}

final class AdminPage
{
    private IntegrationLogger $logger;

    private FulfillmentTypes $types;

    public function __construct(IntegrationLogger $logger, FulfillmentTypes $types)
    {
        $this->logger = $logger;
        $this->types = $types;
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu(): void
    {
        $parent = class_exists('WooCommerce') ? 'woocommerce' : 'tools.php';

        add_submenu_page(
            $parent,
            'Despertando Commerce Core',
            'Despertando Core',
            'manage_woocommerce',
            'dcc-commerce-core',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Sem permissão para acessar esta página.', 'despertando-commerce-core'));
        }

        $logs = $this->logger->recent(20);
        ?>
        <div class="wrap">
            <h1>Despertando Commerce Core</h1>
            <p>Plugin base do Ecommerce Despertando para roteamento de fulfillment, Dimona API, logs e futuras integrações.</p>

            <h2>Fulfillment types</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->types->all() as $key => $definition) : ?>
                    <tr>
                        <td><code><?php echo esc_html($key); ?></code></td>
                        <td><?php echo $definition['enabled'] ? 'Ativo no MVP 1' : 'Planejado para MVP 2'; ?></td>
                        <td><?php echo esc_html($definition['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Logs recentes</h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data</th>
                        <th>Pedido</th>
                        <th>Fonte</th>
                        <th>Evento</th>
                        <th>Nível</th>
                        <th>Mensagem</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($logs === []) : ?>
                    <tr><td colspan="7">Nenhum log registrado ainda.</td></tr>
                <?php else : ?>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html((string) $log->id); ?></td>
                            <td><?php echo esc_html((string) $log->created_at); ?></td>
                            <td><?php echo esc_html((string) $log->order_id); ?></td>
                            <td><?php echo esc_html((string) $log->source); ?></td>
                            <td><?php echo esc_html((string) $log->event); ?></td>
                            <td><?php echo esc_html((string) $log->level); ?></td>
                            <td><?php echo esc_html((string) $log->message); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
