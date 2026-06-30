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
            'default' => 'https://api.camisadimona.com.br',
        ]);
    }

    public function environment(): string
    {
        $value = get_option(self::OPTION_ENVIRONMENT, 'sandbox');

        return in_array($value, ['sandbox', 'production'], true) ? (string) $value : 'sandbox';
    }

    public function baseUrl(): string
    {
        $value = get_option(self::OPTION_BASE_URL, 'https://api.camisadimona.com.br');

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
