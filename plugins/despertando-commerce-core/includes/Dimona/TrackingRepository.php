<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

if (!defined('ABSPATH')) {
    exit;
}

final class TrackingRepository
{
    public static function tableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'dcc_dimona_tracking_events';
    }

    public static function createTable(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charsetCollate = $wpdb->get_charset_collate();
        $table = self::tableName();
        dbDelta("CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT UNSIGNED NULL,
            dimona_order_id VARCHAR(64) NULL,
            carrier VARCHAR(191) NULL,
            tracking_code VARCHAR(191) NULL,
            tracking_url TEXT NULL,
            status VARCHAR(64) NULL,
            status_label VARCHAR(191) NULL,
            event_datetime DATETIME NULL,
            raw_event_hash CHAR(64) NULL,
            payload LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY dimona_order_id (dimona_order_id),
            KEY raw_event_hash (raw_event_hash)
        ) {$charsetCollate};");
    }

    /**
     * @param array<string, mixed> $event
     */
    public function record(array $event): void
    {
        global $wpdb;
        $payload = $this->sanitizePayload($event);
        $hash = hash('sha256', wp_json_encode($payload) ?: serialize($payload));
        $exists = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . self::tableName() . ' WHERE raw_event_hash=%s', $hash));
        if ($exists > 0) {
            return;
        }
        $wpdb->insert(self::tableName(), [
            'order_id' => isset($event['order_id']) ? (int) $event['order_id'] : null,
            'dimona_order_id' => isset($event['dimona_order_id']) ? sanitize_text_field((string) $event['dimona_order_id']) : null,
            'carrier' => isset($event['carrier']) ? sanitize_text_field((string) $event['carrier']) : null,
            'tracking_code' => isset($event['tracking_code']) ? sanitize_text_field((string) $event['tracking_code']) : null,
            'tracking_url' => isset($event['tracking_url']) ? esc_url_raw((string) $event['tracking_url']) : null,
            'status' => isset($event['status']) ? sanitize_key((string) $event['status']) : null,
            'status_label' => isset($event['status_label']) ? sanitize_text_field((string) $event['status_label']) : null,
            'event_datetime' => isset($event['event_datetime']) ? sanitize_text_field((string) $event['event_datetime']) : null,
            'raw_event_hash' => $hash,
            'payload' => wp_json_encode($payload),
            'created_at' => current_time('mysql'),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sanitizePayload(array $payload): array
    {
        if (isset($payload['api_key'])) {
            $payload['api_key'] = '[REDACTED]';
        }
        return $payload;
    }
}
