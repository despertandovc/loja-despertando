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

    private \Despertando\Commerce\Core\Dimona\Settings $dimonaSettings;

    public function __construct(IntegrationLogger $logger, FulfillmentTypes $types, \Despertando\Commerce\Core\Dimona\Settings $dimonaSettings)
    {
        $this->logger = $logger;
        $this->types = $types;
        $this->dimonaSettings = $dimonaSettings;
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

            <h2>Dimona API</h2>
            <form method="post" action="options.php">
                <?php settings_fields('dcc_dimona_settings'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Ambiente</th>
                        <td>
                            <select name="<?php echo esc_attr(\Despertando\Commerce\Core\Dimona\Settings::OPTION_ENVIRONMENT); ?>">
                                <option value="sandbox" <?php selected($this->dimonaSettings->environment(), 'sandbox'); ?>>Sandbox</option>
                                <option value="production" <?php selected($this->dimonaSettings->environment(), 'production'); ?>>Produção</option>
                            </select>
                            <p class="description">Não ativa chamadas reais por si só. A chamada real entra na próxima fase.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Base URL</th>
                        <td><input type="url" class="regular-text" name="<?php echo esc_attr(\Despertando\Commerce\Core\Dimona\Settings::OPTION_BASE_URL); ?>" value="<?php echo esc_attr($this->dimonaSettings->baseUrl()); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Credencial</th>
                        <td>
                            <strong><?php echo esc_html($this->dimonaSettings->statusLabel()); ?></strong>
                            <p class="description">A API key deve ser configurada fora do banco e fora do chat, por constante <code>DCC_DIMONA_API_KEY</code> ou variável de ambiente <code>DIMONA_API_KEY</code>. O valor nunca é exibido.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Create Order real</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(\Despertando\Commerce\Core\Dimona\Settings::OPTION_CREATE_ORDER_ENABLED); ?>" value="1" <?php checked($this->dimonaSettings->isCreateOrderEnabled()); ?>>
                                Permitir criação real de pedido na Dimona
                            </label>
                            <p class="description"><strong>Trava de segurança:</strong> mantenha desligado até o primeiro teste real autorizado.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Dry-run</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(\Despertando\Commerce\Core\Dimona\Settings::OPTION_DRY_RUN); ?>" value="1" <?php checked($this->dimonaSettings->isDryRun()); ?>>
                                Preparar payload sem enviar Create Order
                            </label>
                            <p class="description">Mesmo com API key configurada, o pedido real não é criado enquanto o dry-run estiver ativo.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Webhook URL</th>
                        <td><code><?php echo esc_html($this->dimonaSettings->webhookUrl()); ?></code></td>
                    </tr>
                </table>
                <?php submit_button('Salvar Dimona'); ?>
            </form>

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
