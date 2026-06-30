<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core;

if (!defined('ABSPATH')) {
    exit;
}

final class Plugin
{
    private static ?self $instance = null;

    private IntegrationLogger $logger;

    private FulfillmentTypes $fulfillmentTypes;

    private Dimona\Settings $dimonaSettings;

    private Dimona\HttpClient $dimonaHttpClient;

    private function __construct()
    {
        $this->logger = new IntegrationLogger();
        $this->fulfillmentTypes = new FulfillmentTypes();
        $this->dimonaSettings = new Dimona\Settings();
        $this->dimonaHttpClient = new Dimona\HttpClient($this->dimonaSettings);
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function activate(): void
    {
        IntegrationLogger::createTable();
        update_option('dcc_version', DCC_VERSION, false);
        Dimona\TrackingRepository::createTable();
    }

    public function boot(): void
    {
        $this->fulfillmentTypes->registerHooks();
        $this->dimonaSettings->registerHooks();
        Dimona\ShippingMethod::registerHooks();
        (new Dimona\WebhookController($this->dimonaSettings, $this->logger, new Dimona\TrackingRepository()))->registerHooks();
        (new Dimona\OrderService($this->dimonaSettings, $this->dimonaHttpClient, $this->logger))->registerHooks();
        (new FulfillmentRouter($this->logger))->registerHooks();

        if (is_admin()) {
            (new Dimona\ProductFields())->registerHooks();
            (new Dimona\OrderPanel($this->logger))->registerHooks();
            (new Dimona\ReprocessAction($this->logger))->registerHooks();
            (new AdminPage($this->logger, $this->fulfillmentTypes, $this->dimonaSettings))->registerHooks();
            add_action('admin_notices', [$this, 'renderWooCommerceNotice']);
            add_filter('plugin_action_links_' . DCC_PLUGIN_BASENAME, [$this, 'addActionLinks']);
        }
    }

    /**
     * @param array<int, string> $links
     * @return array<int, string>
     */
    public function addActionLinks(array $links): array
    {
        $url = admin_url('admin.php?page=dcc-commerce-core');
        array_unshift($links, sprintf('<a href="%s">%s</a>', esc_url($url), esc_html__('Painel Core', 'despertando-commerce-core')));

        return $links;
    }

    public function renderWooCommerceNotice(): void
    {
        if ($this->isWooCommerceActive()) {
            return;
        }

        echo '<div class="notice notice-warning"><p>';
        echo esc_html__('Despertando Commerce Core está ativo, mas o WooCommerce não foi detectado. Ative o WooCommerce para habilitar os campos de fulfillment e roteamento de pedidos.', 'despertando-commerce-core');
        echo '</p></div>';
    }

    private function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce') || defined('WC_VERSION');
    }
}
