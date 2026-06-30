<?php

declare(strict_types=1);

namespace Despertando\Commerce\Core\Dimona;

if (!defined('ABSPATH')) {
    exit;
}

final class HttpClient
{
    private Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array<string, mixed>
     */
    public function quoteShipping(string $zipcode, int $quantity): array
    {
        return $this->request('POST', '/api/v2/shipping', [
            'zipcode' => preg_replace('/\D+/', '', $zipcode),
            'quantity' => (string) max(1, $quantity),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload): array
    {
        return $this->request('POST', '/api/v3/order', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function showOrder(string $dimonaOrderId): array
    {
        return $this->request('GET', '/api/v2/order/' . rawurlencode($dimonaOrderId));
    }

    /**
     * @return array<string, mixed>
     */
    public function tracking(string $dimonaOrderId): array
    {
        return $this->request('GET', '/api/v2/order/' . rawurlencode($dimonaOrderId) . '/tracking');
    }

    /**
     * @return array<string, mixed>
     */
    public function timeline(string $dimonaOrderId): array
    {
        return $this->request('GET', '/api/v2/order/' . rawurlencode($dimonaOrderId) . '/timeline');
    }

    /**
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $body = null): array
    {
        $apiKey = $this->settings->apiKey();
        if ($apiKey === '') {
            return [
                'ok' => false,
                'status_code' => 0,
                'body' => null,
                'error' => 'dimona_api_key_missing',
            ];
        }

        $args = [
            'method' => $method,
            'timeout' => 25,
            'headers' => [
                'api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if ($body !== null) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($this->settings->baseUrl() . $path, $args);
        if (is_wp_error($response)) {
            return [
                'ok' => false,
                'status_code' => 0,
                'body' => null,
                'error' => $response->get_error_message(),
            ];
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $rawBody = (string) wp_remote_retrieve_body($response);
        $decoded = null;
        if ($rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decoded = $rawBody;
            }
        }

        return [
            'ok' => $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'body' => $decoded,
            'error' => $statusCode >= 200 && $statusCode < 300 ? null : $this->sanitizeErrorBody($decoded),
        ];
    }

    /**
     * @param mixed $decoded
     */
    private function sanitizeErrorBody($decoded): string
    {
        if (is_string($decoded)) {
            return $decoded;
        }

        if (is_array($decoded)) {
            unset($decoded['api_key'], $decoded['api-key'], $decoded['token'], $decoded['authorization']);
            return wp_json_encode($decoded) ?: 'dimona_api_error';
        }

        return 'dimona_api_error';
    }
}
