<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShiprocketService
{
    protected string $baseUrl = 'https://apiv2.shiprocket.in/v1/external';

    /**
     * Check whether Shiprocket credentials are configured.
     */
    public function isConfigured(): bool
    {
        $email = trim(Setting::get('shiprocket_email', config('services.shiprocket.email', '')));
        $password = trim(Setting::get('shiprocket_password', config('services.shiprocket.password', '')));
        return !empty($email) && !empty($password);
    }

    /**
     * Authenticate and retrieve cached JWT token from Shiprocket.
     */
    public function getToken(): string
    {
        return Cache::remember('shiprocket_jwt_token', 86400 * 9, function () {
            $email = trim(Setting::get('shiprocket_email', config('services.shiprocket.email', '')));
            $password = trim(Setting::get('shiprocket_password', config('services.shiprocket.password', '')));

            if (empty($email) || empty($password)) {
                throw new \Exception('Shiprocket email or password is not configured in Admin Settings.');
            }

            $response = Http::post("{$this->baseUrl}/auth/login", [
                'email'    => $email,
                'password' => $password,
            ]);

            if ($response->successful() && $token = $response->json('token')) {
                return $token;
            }

            Log::error('Shiprocket Auth Error: ' . $response->body());
            throw new \Exception('Shiprocket Authentication Failed: ' . ($response->json('message') ?? 'Invalid Credentials'));
        });
    }

    /**
     * Create an order/shipment in Shiprocket (Manual trigger by Admin).
     */
    public function createShipment(Order $order): array
    {
        $token = $this->getToken();
        $pickupLocation = trim(Setting::get('shiprocket_pickup_location', 'Primary'));

        $orderItems = [];
        $totalWeight = 0;
        $maxLength = 0; 
        $maxWidth = 0; 
        $maxHeight = 0;

        foreach ($order->items as $item) {
            $product = $item->product;
            $unitWeight = $product && $product->weight > 0 ? (float) $product->weight : 0.500; // default 500g for tractor parts
            $itemWeight = $unitWeight * $item->quantity;
            $totalWeight += $itemWeight;

            if ($product) {
                $maxLength = max($maxLength, (int) ($product->length ?? 10));
                $maxWidth  = max($maxWidth,  (int) ($product->width ?? 10));
                $maxHeight = max($maxHeight, (int) ($product->height ?? 10));
            }

            $sku = $product && !empty($product->sku) ? $product->sku : 'MTM-PROD-' . $item->product_id;

            $orderItems[] = [
                'name'          => $item->product_name,
                'sku'           => $sku,
                'units'         => (int) $item->quantity,
                'selling_price' => (float) $item->unit_price,
            ];
        }

        // Customer name formatting
        $nameParts = explode(' ', trim($order->shipping_name), 2);
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? 'Customer';

        // Clean phone number to 10 digits
        $cleanPhone = preg_replace('/[^0-9]/', '', $order->shipping_phone);
        if (strlen($cleanPhone) > 10) {
            $cleanPhone = substr($cleanPhone, -10);
        }

        // Payment method mapping
        $isCod = strtolower($order->payment_method) === 'cod';
        $paymentMethod = $isCod ? 'COD' : 'Prepaid';

        $payload = [
            'order_id'              => $order->order_number,
            'order_date'            => $order->created_at->format('Y-m-d H:i'),
            'pickup_location'       => $pickupLocation,
            'billing_customer_name' => $firstName,
            'billing_last_name'     => $lastName,
            'billing_address'       => $order->shipping_address,
            'billing_city'          => $order->shipping_city,
            'billing_pincode'       => $order->shipping_zip,
            'billing_state'         => $order->shipping_state,
            'billing_country'       => 'India',
            'billing_email'         => $order->shipping_email,
            'billing_phone'         => $cleanPhone,
            'shipping_is_billing'   => true,
            'order_items'           => $orderItems,
            'payment_method'        => $paymentMethod,
            'sub_total'             => (float) $order->total_amount,
            'length'                => max(10, $maxLength),
            'breadth'               => max(10, $maxWidth),
            'width'                 => max(10, $maxWidth),
            'height'                => max(5, $maxHeight),
            'weight'                => max(0.2, round($totalWeight, 3)),
        ];

        Log::info('Shiprocket: Creating shipment order #' . $order->order_number, ['payload' => $payload]);

        $response = Http::withToken($token)->post("{$this->baseUrl}/orders/create/adhoc", $payload);

        if (!$response->successful()) {
            Log::error('Shiprocket Create Order Failed: ' . $response->body());
            $errorMsg = $response->json('message') ?? 'Failed to create order on Shiprocket.';
            if (is_array($response->json('errors'))) {
                $errorMsg .= ' - ' . json_encode($response->json('errors'));
            }
            throw new \Exception($errorMsg);
        }

        $data = $response->json();

        Log::info('Shiprocket Create Order Response for #' . $order->order_number . ': ' . $response->body());

        if (!is_array($data)) {
            throw new \Exception('Invalid response from Shiprocket: ' . $response->body());
        }

        $shiprocketOrderId = $data['order_id'] 
            ?? ($data['data']['order_id'] 
            ?? ($data['id'] 
            ?? null));

        $shiprocketShipmentId = $data['shipment_id'] 
            ?? ($data['data']['shipment_id'] 
            ?? ($data['shipment_orders'][0]['id'] 
            ?? $shiprocketOrderId));

        $awbCode     = $data['awb_code'] ?? ($data['data']['awb_code'] ?? null);
        $courierName = $data['courier_name'] ?? ($data['data']['courier_name'] ?? null);

        if (!$shiprocketOrderId && !$shiprocketShipmentId) {
            throw new \Exception('Shiprocket response did not contain an order_id or shipment_id: ' . $response->body());
        }

        $order->forceFill([
            'shiprocket_order_id'     => (string) $shiprocketOrderId,
            'shiprocket_shipment_id'  => (string) $shiprocketShipmentId,
            'shiprocket_awb_code'     => $awbCode ? (string) $awbCode : $order->shiprocket_awb_code,
            'shiprocket_status'       => 'PROCESSING',
            'shiprocket_courier_name' => $courierName ? (string) $courierName : $order->shiprocket_courier_name,
            'status'                  => $order->status === 'pending' ? 'processing' : $order->status,
        ])->save();

        return [
            'success'     => true,
            'order_id'    => $shiprocketOrderId,
            'shipment_id' => $shiprocketShipmentId,
            'awb_code'    => $awbCode,
        ];
    }

    /**
     * Generate AWB (Air Waybill) Code for an existing shipment.
     */
    public function generateAwb(Order $order): array
    {
        $shipmentId = $order->shiprocket_shipment_id ?: $order->shiprocket_order_id;
        if (!$shipmentId) {
            throw new \Exception('Shipment ID is missing. Please create the shipment first.');
        }

        $token = $this->getToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/courier/assign/awb", [
            'shipment_id' => $shipmentId,
        ]);

        if (!$response->successful()) {
            Log::error('Shiprocket Generate AWB Error: ' . $response->body());
            throw new \Exception($response->json('message') ?? 'Failed to generate AWB: ' . $response->body());
        }

        $data = $response->json('response.data') ?? $response->json('data') ?? $response->json();
        $awbCode = $data['awb_code'] ?? ($data['response']['data']['awb_code'] ?? null);
        $courierName = $data['courier_name'] ?? ($data['response']['data']['courier_name'] ?? null);

        if ($awbCode) {
            $order->forceFill([
                'shiprocket_awb_code'     => (string) $awbCode,
                'shiprocket_courier_name' => $courierName ? (string) $courierName : $order->shiprocket_courier_name,
                'shiprocket_status'       => 'AWB_ASSIGNED',
                'status'                  => 'shipped',
            ])->save();
        }

        return [
            'success'      => true,
            'awb_code'     => $awbCode,
            'courier_name' => $courierName,
        ];
    }

    /**
     * Request Courier Pickup for ready packages.
     */
    public function requestPickup(Order $order): array
    {
        if (!$order->shiprocket_shipment_id) {
            throw new \Exception('Shipment ID missing for pickup request.');
        }

        $token = $this->getToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/courier/generate/pickup", [
            'shipment_id' => [$order->shiprocket_shipment_id],
        ]);

        if (!$response->successful()) {
            throw new \Exception($response->json('message') ?? 'Pickup request failed.');
        }

        $order->update([
            'shiprocket_status' => 'PICKUP_SCHEDULED',
        ]);

        return $response->json();
    }

    /**
     * Track Order / AWB live location & status.
     */
    public function trackShipment(Order $order): array
    {
        $token = $this->getToken();

        $url = $order->shiprocket_awb_code 
            ? "{$this->baseUrl}/courier/track/awb/{$order->shiprocket_awb_code}"
            : "{$this->baseUrl}/courier/track/shipment/{$order->shiprocket_shipment_id}";

        $response = Http::withToken($token)->get($url);

        if ($response->successful()) {
            $trackData = $response->json('tracking_data') ?? [];
            $currentStatus = $trackData['track_status'] ?? ($trackData['shipment_track'][0]['current_status'] ?? null);

            if ($currentStatus) {
                $order->update(['shiprocket_status' => strtoupper($currentStatus)]);
            }

            return [
                'success' => true,
                'status'  => $currentStatus ?? $order->shiprocket_status,
                'data'    => $trackData,
            ];
        }

        throw new \Exception('Unable to fetch live tracking details.');
    }

    /**
     * Generate & Download Shipping Label URL.
     */
    public function printLabel(Order $order): ?string
    {
        if (!$order->shiprocket_shipment_id) {
            throw new \Exception('Shipment ID is missing.');
        }

        $token = $this->getToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/courier/generate/label", [
            'shipment_id' => [$order->shiprocket_shipment_id],
        ]);

        if ($response->successful()) {
            return $response->json('label_url');
        }

        throw new \Exception($response->json('message') ?? 'Could not generate label.');
    }

    /**
     * Cancel Shipment in Shiprocket.
     */
    public function cancelShipment(Order $order): array
    {
        if (!$order->shiprocket_order_id) {
            return ['success' => false, 'message' => 'Order not synced with Shiprocket.'];
        }

        $token = $this->getToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/orders/cancel", [
            'ids' => [$order->shiprocket_order_id],
        ]);

        if ($response->successful()) {
            $order->update([
                'shiprocket_status' => 'CANCELLED',
            ]);
            return ['success' => true];
        }

        throw new \Exception($response->json('message') ?? 'Shipment cancellation failed on Shiprocket.');
    }

    /**
     * Check Courier Serviceability and get lowest estimated shipping rate for a destination pincode.
     */
    public function checkServiceabilityAndRate(
        string $deliveryPincode, 
        float $weight = 0.5, 
        bool $isCod = false,
        int $length = 10,
        int $width = 10,
        int $height = 10
    ): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Shiprocket credentials are not configured.',
                'rate'    => null,
            ];
        }

        $pickupPincode = trim(Setting::get('shiprocket_pickup_pincode', ''));
        
        if (empty($pickupPincode)) {
            return [
                'success' => false,
                'message' => 'Pickup pincode is not configured in Admin Settings.',
                'rate'    => null,
            ];
        }

        try {
            $token = $this->getToken();

            $queryParams = [
                'pickup_postcode'   => $pickupPincode,
                'delivery_postcode' => $deliveryPincode,
                'weight'            => max(0.1, round($weight, 3)),
                'cod'               => $isCod ? 1 : 0,
            ];

            if ($length > 0) $queryParams['length']  = max(1, $length);
            if ($width > 0) {
                $queryParams['breadth'] = max(1, $width);
                $queryParams['width']   = max(1, $width);
            }
            if ($height > 0) $queryParams['height']  = max(1, $height);

            $response = Http::withToken($token)->get("{$this->baseUrl}/courier/serviceability/", $queryParams);

            if ($response->status() === 401) {
                Cache::forget('shiprocket_jwt_token');
            }

            if ($response->successful()) {
                $couriers = $response->json('data.available_courier_companies') ?? [];
                
                if (!empty($couriers)) {
                    // Sort couriers by rate ascending to find the best available rate
                    usort($couriers, function ($a, $b) {
                        return ($a['rate'] ?? 0) <=> ($b['rate'] ?? 0);
                    });

                    $cheapest = $couriers[0];
                    return [
                        'success'                 => true,
                        'rate'                    => round((float) ($cheapest['rate'] ?? 0), 2),
                        'courier_name'            => $cheapest['courier_name'] ?? 'Shiprocket Express',
                        'estimated_delivery_days' => $cheapest['estimated_delivery_days'] ?? null,
                        'etd'                     => $cheapest['etd'] ?? null,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'No courier service available for this pincode.',
                    'rate'    => null,
                ];
            }

            Log::warning('Shiprocket Serviceability check failed: ' . $response->body());
            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Serviceability check failed.',
                'rate'    => null,
            ];
        } catch (\Exception $e) {
            Log::warning('Shiprocket Serviceability Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'rate'    => null,
            ];
        }
    }
}

