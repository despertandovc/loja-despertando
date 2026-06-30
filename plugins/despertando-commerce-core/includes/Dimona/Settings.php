<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

if (!defined('ABSPATH')) {
    exit;
}

final class Settings
{
    public const OPTION_ENVIRONMENT = 'dcc_dimona_environment';
    public const OPTION_BASE_URL = 'dcc_dimona_base_url';
    public const OPTION_CREATE_ORDER_ENABLED = 'dcc_dimona_create_order_enabled';
    public const OPTION_DRY_RUN = 'dcc_dimona_dry_run';

    public function registerHooks(): void
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function registerSettings(): void
    {
        register_setting('dcc_dimona_settings', self::OPTION_ENVIRONMENT, [
            'type' => 'string',
            'sanitize_callback' => static fn ($value): string => in_array((string) $value, ['sandbox', 'production'], true) ? (string) $value : 'sandbox',
            'default' => 'sandbox',
        ]);

        register_setting('dcc_dimona_settings', self::OPTION_BASE_URL, [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://admin.camisadimona.com.br',
        ]);

        register_setting('dcc_dimona_settings', self::OPTION_CREATE_ORDER_ENABLED, [
            'type' => 'boolean',
            'sanitize_callback' => static fn ($value): bool => (bool) $value,
            'default' => false,
        ]);

        register_setting('dcc_dimona_settings', self::OPTION_DRY_RUN, [
            'type' => 'boolean',
            'sanitize_callback' => static fn ($value): bool => (bool) $value,
            'default' => true,
        ]);
    }

    public function environment(): string
    {
        $value = get_option(self::OPTION_ENVIRONMENT, 'sandbox');

        return in_array($value, ['sandbox', 'production'], true) ? (string) $value : 'sandbox';
    }

    public function baseUrl(): string
    {
        $value = get_option(self::OPTION_BASE_URL, 'https://admin.camisadimona.com.br');

        return rtrim(esc_url_raw((string) $value), '/');
    }

    public function hasApiKey(): bool
    {
        if (defined('DCC_DIMONA_API_KEY') && is_string(DCC_DIMONA_API_KEY) && DCC_DIMONA_API_KEY !== '') {
            return true;
        }

        $envValue = getenv('DIMONA_API_KEY');

        return is_string($envValue) && $envValue !== '';
    }


    public function apiKey(): string
    {
        if (defined('DCC_DIMONA_API_KEY') && is_string(DCC_DIMONA_API_KEY)) {
            return (string) DCC_DIMONA_API_KEY;
        }

        $envValue = getenv('DIMONA_API_KEY');
        return is_string($envValue) ? $envValue : '';
    }

    public function isCreateOrderEnabled(): bool
    {
        return (bool) get_option(self::OPTION_CREATE_ORDER_ENABLED, false);
    }

    public function isDryRun(): bool
    {
        return (bool) get_option(self::OPTION_DRY_RUN, true);
    }

    public function webhookUrl(): string
    {
        return rest_url('despertando-commerce/v1/dimona/webhook');
    }

    public function credentialSource(): string
    {
        if (defined('DCC_DIMONA_API_KEY') && is_string(DCC_DIMONA_API_KEY) && DCC_DIMONA_API_KEY !== '') {
            return 'DCC_DIMONA_API_KEY constant';
        }

        $envValue = getenv('DIMONA_API_KEY');
        if (is_string($envValue) && $envValue !== '') {
            return 'DIMONA_API_KEY environment variable';
        }

        return 'not configured';
    }

    public function statusLabel(): string
    {
        return $this->hasApiKey() ? 'Credencial configurada fora do WordPress' : 'Credencial ainda não configurada';
    }
}
