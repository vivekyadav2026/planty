<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashfreeService
{
    protected string $appId;
    protected string $secretKey;
    protected string $mode;
    protected string $apiVersion;
    protected string $baseUrl;

    public function __construct()
    {
        // Settings table takes precedence if configured by admin, otherwise config/env
        $this->appId = trim(Setting::get('cashfree_app_id', config('services.cashfree.app_id', '')));
        $this->secretKey = trim(Setting::get('cashfree_secret_key', config('services.cashfree.secret_key', '')));
        $this->mode = strtolower(trim(Setting::get('cashfree_mode', config('services.cashfree.mode', 'sandbox'))));
        $this->apiVersion = config('services.cashfree.api_version', '2023-08-01');

        if ($this->mode === 'production' || $this->mode === 'live') {
            $this->baseUrl = 'https://api.cashfree.com/pg';
        } elseif (str_starts_with($this->appId, 'TEST') || str_starts_with($this->secretKey, 'cfsk_ma_test_')) {
            $this->baseUrl = 'https://sandbox.cashfree.com/pg';
            $this->mode = 'sandbox';
        } elseif (!empty($this->appId) && !str_starts_with($this->appId, 'TEST')) {
            // Live keys start with numeric merchant ID or prod prefix
            $this->baseUrl = 'https://api.cashfree.com/pg';
            $this->mode = 'production';
        } else {
            $this->baseUrl = 'https://sandbox.cashfree.com/pg';
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->appId) && !empty($this->secretKey) && $this->appId !== 'your_cashfree_app_id_here';
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Create an order in Cashfree
     */
    public function createOrder(array $orderData): array
    {
        $url = $this->baseUrl . '/orders';

        $returnUrl = $orderData['return_url'] ?? route('checkout.cashfree.callback') . '?order_id={order_id}';
        $notifyUrl = $orderData['notify_url'] ?? route('webhook.cashfree');

        // Cashfree API strictly enforces https:// protocol on all webhook and callback endpoints
        if (str_starts_with($returnUrl, 'http://127.0.0.1') || str_starts_with($returnUrl, 'http://localhost')) {
            $returnUrl = 'https://mahadevtractor.com/checkout/cashfree-callback?order_id={order_id}';
        } elseif (str_starts_with($returnUrl, 'http://')) {
            $returnUrl = 'https://' . substr($returnUrl, 7);
        }

        if (str_starts_with($notifyUrl, 'http://127.0.0.1') || str_starts_with($notifyUrl, 'http://localhost')) {
            $notifyUrl = 'https://mahadevtractor.com/webhook/cashfree';
        } elseif (str_starts_with($notifyUrl, 'http://')) {
            $notifyUrl = 'https://' . substr($notifyUrl, 7);
        }

        $payload = [
            'order_id' => (string) $orderData['order_id'],
            'order_amount' => (float) $orderData['order_amount'],
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id' => (string) ($orderData['customer_id'] ?? 'CUST_' . time()),
                'customer_name' => $orderData['customer_name'] ?? 'Customer',
                'customer_email' => $orderData['customer_email'] ?? 'customer@example.com',
                'customer_phone' => $this->sanitizePhone($orderData['customer_phone'] ?? '9999999999'),
            ],
            'order_meta' => [
                'return_url' => $returnUrl,
                'notify_url' => $notifyUrl,
            ],
            'order_note' => $orderData['order_note'] ?? 'Order #' . $orderData['order_id'],
        ];

        Log::info('Cashfree: Creating order request', ['url' => $url, 'order_id' => $payload['order_id']]);

        $response = Http::withHeaders([
            'x-client-id' => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version' => $this->apiVersion,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($url, $payload);

        if (!$response->successful()) {
            Log::error('Cashfree: Create order failed', [
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);
            $errMessage = $response->json('message') ?? 'Failed to create Cashfree order (HTTP ' . $response->status() . ')';
            throw new \Exception($errMessage);
        }

        return $response->json();
    }

    /**
     * Fetch order details and status from Cashfree
     */
    public function getOrder(string $orderId): array
    {
        $url = $this->baseUrl . '/orders/' . urlencode($orderId);

        $response = Http::withHeaders([
            'x-client-id' => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version' => $this->apiVersion,
            'Accept' => 'application/json',
        ])->get($url);

        if (!$response->successful()) {
            Log::error('Cashfree: Get order failed', [
                'order_id' => $orderId,
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);
            throw new \Exception($response->json('message') ?? 'Failed to fetch Cashfree order');
        }

        return $response->json();
    }

    /**
     * Fetch payments for an order to find payment ID and details
     */
    public function getOrderPayments(string $orderId): array
    {
        $url = $this->baseUrl . '/orders/' . urlencode($orderId) . '/payments';

        $response = Http::withHeaders([
            'x-client-id' => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version' => $this->apiVersion,
            'Accept' => 'application/json',
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Verify Cashfree webhook signature
     */
    public function verifyWebhookSignature(string $rawBody, ?string $timestamp, ?string $signature): bool
    {
        if (empty($signature) || empty($timestamp) || empty($this->secretKey)) {
            return false;
        }

        $expectedData = $timestamp . $rawBody;
        $computedSignature = base64_encode(hash_hmac('sha256', $expectedData, $this->secretKey, true));

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Clean phone number to 10 digits as required by Cashfree
     */
    protected function sanitizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) > 10) {
            $cleaned = substr($cleaned, -10);
        }
        return (strlen($cleaned) >= 10) ? $cleaned : '9999999999';
    }
}
