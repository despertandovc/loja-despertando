<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core;

if (!defined('ABSPATH')) {
    exit;
}

final class IntegrationLogger
{
    public static function tableName(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'dcc_integration_logs';
    }

    public static function createTable(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();
        $table = self::tableName();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT UNSIGNED NULL,
            source VARCHAR(64) NOT NULL DEFAULT 'core',
            event VARCHAR(120) NOT NULL,
            level VARCHAR(20) NOT NULL DEFAULT 'info',
            message TEXT NOT NULL,
            context LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY order_id (order_id),
            KEY source (source),
            KEY event (event),
            KEY created_at (created_at)
        ) {$charsetCollate};";

        dbDelta($sql);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function log(string $event, string $message, array $context = [], ?int $orderId = null, string $source = 'core', string $level = 'info'): void
    {
        global $wpdb;

        $safeContext = $this->sanitizeContext($context);

        $wpdb->insert(
            self::tableName(),
            [
                'order_id' => $orderId,
                'source' => sanitize_key($source),
                'event' => sanitize_key($event),
                'level' => sanitize_key($level),
                'message' => wp_strip_all_tags($message),
                'context' => wp_json_encode($safeContext),
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * @return array<int, object>
     */
    public function recent(int $limit = 20): array
    {
        global $wpdb;

        $limit = max(1, min(100, $limit));
        $table = self::tableName();

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} ORDER BY id DESC LIMIT %d", $limit));
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function sanitizeContext(array $context): array
    {
        $blockedKeys = ['token', 'access_token', 'secret', 'password', 'authorization', 'api_key', 'apikey'];
        $safe = [];

        foreach ($context as $key => $value) {
            $normalizedKey = strtolower((string) $key);
            if (in_array($normalizedKey, $blockedKeys, true)) {
                $safe[$key] = '[REDACTED]';
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $safe[$key] = $value;
                continue;
            }

            $safe[$key] = '[complex_value]';
        }

        return $safe;
    }
}
