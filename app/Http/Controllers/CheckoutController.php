<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Services\CashfreeService;
use App\Services\ShiprocketService;
use App\Models\Setting;

class CheckoutController extends Controller
{
    // Show Checkout Form
    public function index()
    {
        if (auth()->user() && auth()->user()->is_admin) {
            return redirect()->route('cart.index')->with('error', 'Admins are not allowed to place orders.');
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty! Please add products before checking out.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Determine default or saved pincode if available
        $defaultPincode = null;
        if (auth()->check()) {
            $defaultAddr = auth()->user()->addresses()->where('is_default', true)->first() 
                ?? auth()->user()->addresses()->first();
            $defaultPincode = $defaultAddr ? $defaultAddr->zip : auth()->user()->zip;
        }

        $shippingData = $this->computeDeliveryCharge('online_delivery', $defaultPincode);
        $initialDeliveryCharge = $shippingData['charge'];
        $initialGrandTotal = $subtotal + $initialDeliveryCharge;

        return view('frontend.checkout', compact(
            'cart', 
            'subtotal', 
            'initialDeliveryCharge', 
            'initialGrandTotal', 
            'shippingData'
        ));
    }

    /**
     * Compute delivery charge based on delivery type, pincode, subtotal, and cart items.
     */
    public function computeDeliveryCharge(string $deliveryType, ?string $pincode = null, bool $isCod = false): array
    {
        if ($deliveryType === 'self_pickup') {
            return [
                'charge'       => 0.00,
                'is_free'      => true,
                'courier_name' => 'Self Pickup',
                'message'      => 'Self Pickup from store (No delivery charge)',
                'etd'          => null,
            ];
        }

        $cart = session()->get('cart', []);
        $subtotal = 0;
        $totalWeight = 0;
        $maxLength = 0;
        $maxWidth  = 0;
        $maxHeight = 0;

        foreach ($cart as $productId => $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $product = Product::find($productId);
            $weight = $product && $product->weight > 0 ? (float) $product->weight : 0.500;
            $totalWeight += $weight * $item['quantity'];

            if ($product) {
                $maxLength = max($maxLength, (int) ($product->length ?? 10));
                $maxWidth  = max($maxWidth,  (int) ($product->width ?? 10));
                $maxHeight = max($maxHeight, (int) ($product->height ?? 10));
            }
        }

        // Check for Free Shipping threshold
        $freeThreshold = (float) Setting::get('free_shipping_threshold', 0);
        if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
            return [
                'charge'       => 0.00,
                'is_free'      => true,
                'courier_name' => 'Free Delivery',
                'message'      => 'Free Delivery on orders above ₹' . number_format($freeThreshold, 0),
                'etd'          => null,
            ];
        }

        // Check Shiprocket if delivery pincode is a valid 6-digit Indian pincode
        $cleanPincode = preg_replace('/[^0-9]/', '', (string) $pincode);
        if (strlen($cleanPincode) === 6) {
            try {
                $shiprocket = new ShiprocketService();
                $srResult = $shiprocket->checkServiceabilityAndRate(
                    $cleanPincode, 
                    $totalWeight, 
                    $isCod, 
                    max(10, $maxLength), 
                    max(10, $maxWidth), 
                    max(5, $maxHeight)
                );

                if (!empty($srResult['success']) && isset($srResult['rate']) && $srResult['rate'] > 0) {
                    $daysText = !empty($srResult['estimated_delivery_days']) ? " ({$srResult['estimated_delivery_days']} days)" : '';
                    return [
                        'charge'       => (float) $srResult['rate'],
                        'is_free'      => false,
                        'courier_name' => $srResult['courier_name'] ?? 'Shiprocket Express',
                        'message'      => 'Delivered via ' . ($srResult['courier_name'] ?? 'Shiprocket') . $daysText,
                        'etd'          => $srResult['etd'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::info('Shipping calculation falling back to default: ' . $e->getMessage());
            }
        }

        // Fallback to default delivery charge configured in Admin Settings
        $defaultCharge = (float) Setting::get('default_delivery_charge', 99);
        return [
            'charge'       => $defaultCharge,
            'is_free'      => $defaultCharge <= 0,
            'courier_name' => 'Standard Courier',
            'message'      => $defaultCharge <= 0 ? 'Free Standard Delivery' : 'Standard Delivery Charge',
            'etd'          => null,
        ];
    }

    /**
     * AJAX endpoint to calculate and return dynamic shipping charge for checkout
     */
    public function calculateShipping(Request $request)
    {
        $pincode       = $request->input('pincode');
        $deliveryType  = $request->input('delivery_type', 'online_delivery');
        $paymentMethod = $request->input('payment_method', 'cashfree');
        $isCod         = strtolower((string) $paymentMethod) === 'cod';

        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shippingData = $this->computeDeliveryCharge($deliveryType, $pincode, $isCod);
        $deliveryCharge = (float) $shippingData['charge'];
        $grandTotal = $subtotal + $deliveryCharge;

        return response()->json([
            'success'          => true,
            'subtotal'         => $subtotal,
            'delivery_charge'  => $deliveryCharge,
            'formatted_charge' => $shippingData['is_free'] ? 'FREE' : '₹' . number_format($deliveryCharge, 2),
            'is_free'          => $shippingData['is_free'],
            'grand_total'      => $grandTotal,
            'formatted_total'  => '₹' . number_format($grandTotal, 2),
            'courier_name'     => $shippingData['courier_name'],
            'message'          => $shippingData['message'],
            'etd'              => $shippingData['etd'],
        ]);
    }

    // Place Order
    public function store(Request $request)
    {
        if (auth()->user() && auth()->user()->is_admin) {
            return redirect()->route('cart.index')->with('error', 'Admins are not allowed to place orders.');
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Validate stock availability for all cart items
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || $product->quantity < $item['quantity']) {
                $available = $product ? $product->quantity : 0;
                return redirect()->route('cart.index')->with('error', "Cannot complete order: '{$item['name']}' only has {$available} unit(s) left in stock.");
            }
        }

        $request->merge([
            'delivery_type' => $request->input('delivery_type', 'online_delivery')
        ]);

        // Validate basic info
        $validated = $request->validate([
            'shipping_name'    => 'required|string|max:255',
            'shipping_email'   => 'required|email|max:255',
            'shipping_phone'   => 'required|string|max:20',
            'delivery_type'    => 'required|string|in:online_delivery,self_pickup',
            'payment_method'   => 'required|string|in:cod,stripe,cashfree',
            'notes'            => 'nullable|string',
        ]);

        $selectedAddressId = $request->input('selected_address_id');
        $drivingLicensePath = null;
        $salesTaxPermitPath = null;

        if ($validated['delivery_type'] === 'self_pickup') {
            $shippingAddress  = \App\Models\Setting::get('site_address', 'Mahadev Tractor Workshop & Store');
            $shippingAddress2 = 'Self Pickup';
            $shippingCity     = \App\Models\Setting::get('site_city', 'Store City');
            $shippingState    = \App\Models\Setting::get('site_state', 'Store State');
            $shippingZip      = \App\Models\Setting::get('site_zip', '000000');
        } else {
            // Check if user selected a saved address
            if (auth()->check() && $selectedAddressId && $selectedAddressId !== 'new') {
                $savedAddress = auth()->user()->addresses()->find($selectedAddressId);
                if ($savedAddress) {
                    $shippingAddress    = $savedAddress->address;
                    $shippingAddress2   = $savedAddress->address2;
                    $shippingCity       = $savedAddress->city;
                    $shippingState      = $savedAddress->state;
                    $shippingZip        = $savedAddress->zip;
                    $drivingLicensePath = $savedAddress->driving_license;
                    $salesTaxPermitPath = $savedAddress->sales_tax_permit;
                } else {
                    abort(400, 'Invalid address selection.');
                }
            } else {
                // Validate address details if online delivery
                $rules = [
                    'shipping_address'  => 'required|string',
                    'shipping_address2' => 'nullable|string',
                    'shipping_city'     => 'required|string|max:100',
                    'shipping_state'    => 'required|string|max:100',
                    'shipping_zip'      => 'required|string|max:10',
                ];

                if (auth()->check()) {
                    $rules['driving_license'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                    $rules['sales_tax_permit'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
                }

                $addressValidated = $request->validate($rules);

                $shippingAddress  = $addressValidated['shipping_address'];
                $shippingAddress2 = $addressValidated['shipping_address2'] ?? '';
                $shippingCity     = $addressValidated['shipping_city'];
                $shippingState    = $addressValidated['shipping_state'];
                $shippingZip      = $addressValidated['shipping_zip'];

                if (auth()->check()) {
                    if ($request->hasFile('driving_license')) {
                        $file = $request->file('driving_license');
                        $filename = time() . '_dl_' . $file->getClientOriginalName();
                        $file->move(public_path('uploads/documents'), $filename);
                        $drivingLicensePath = 'uploads/documents/' . $filename;
                    }

                    if ($request->hasFile('sales_tax_permit')) {
                        $file = $request->file('sales_tax_permit');
                        $filename = time() . '_st_' . $file->getClientOriginalName();
                        $file->move(public_path('uploads/documents'), $filename);
                        $salesTaxPermitPath = 'uploads/documents/' . $filename;
                    }

                    // Save as a new saved address for the user
                    $isFirst = auth()->user()->addresses()->count() === 0;
                    $newAddr = auth()->user()->addresses()->create([
                        'phone' => $validated['shipping_phone'],
                        'address' => $shippingAddress,
                        'address2' => $shippingAddress2,
                        'city' => $shippingCity,
                        'state' => $shippingState,
                        'zip' => $shippingZip,
                        'driving_license' => $drivingLicensePath,
                        'sales_tax_permit' => $salesTaxPermitPath,
                        'is_default' => $isFirst || $request->has('is_default'),
                    ]);

                    if ($newAddr->is_default) {
                        auth()->user()->addresses()->where('id', '!=', $newAddr->id)->update(['is_default' => false]);
                    }
                }
            }
        }

        // Merge address lines for legacy fields
        $fullAddress = trim($shippingAddress . ($shippingAddress2 ? ', ' . $shippingAddress2 : ''));

        // Auto-save user profile address if logged in and it's online delivery
        if (auth()->check() && $validated['delivery_type'] === 'online_delivery') {
            auth()->user()->update([
                'phone'   => $validated['shipping_phone'],
                'address' => $fullAddress,
                'city'    => $shippingCity,
                'state'   => $shippingState,
                'zip'     => $shippingZip,
            ]);
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Compute delivery charge
        $shippingCalc   = $this->computeDeliveryCharge($validated['delivery_type'], $shippingZip, $validated['payment_method'] === 'cod');
        $deliveryCharge = (float) $shippingCalc['charge'];
        $grandTotal     = $subtotal + $deliveryCharge;

        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        // Create Order
        $order = Order::create([
            'user_id'          => auth()->id(),
            'order_number'     => $orderNumber,
            'total_amount'     => $grandTotal,
            'delivery_charge'  => $deliveryCharge,
            'status'           => 'pending',
            'payment_status'   => 'pending',
            'payment_method'   => $validated['payment_method'],
            'delivery_type'    => $validated['delivery_type'],
            'notes'            => $validated['notes'] ?? null,
            'shipping_name'    => $validated['shipping_name'],
            'shipping_email'   => $validated['shipping_email'],
            'shipping_phone'   => $validated['shipping_phone'],
            'shipping_address' => $fullAddress,
            'shipping_city'    => $shippingCity,
            'shipping_state'   => $shippingState,
            'shipping_zip'     => $shippingZip,
            'driving_license'  => $drivingLicensePath,
            'sales_tax_permit' => $salesTaxPermitPath,
        ]);

        // Create Order Items and decrement stock
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $product->decrement('quantity', $item['quantity']);
            }
            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $productId,
                'product_name' => $item['name'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['price'],
                'total_price'  => $item['price'] * $item['quantity'],
            ]);
        }

        // --- STRIPE PAYMENT ---
        if ($validated['payment_method'] === 'stripe') {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));

                $paymentIntent = PaymentIntent::create([
                    'amount'      => (int) round($order->total_amount * 100), // cents
                    'currency'    => 'usd',
                    'description' => 'Mahadev Tractor Order #' . $order->order_number,
                    'metadata'    => [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                    ],
                    'automatic_payment_methods' => ['enabled' => true],
                ]);

                $order->update([
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'stripe_client_secret'     => $paymentIntent->client_secret,
                ]);

                return view('frontend.stripe_payment', [
                    'order'           => $order,
                    'clientSecret'    => $paymentIntent->client_secret,
                    'stripePublicKey' => config('services.stripe.key'),
                ]);

            } catch (\Exception $e) {
                $order->update([
                    'status' => 'failed',
                    'notes'  => 'Stripe PaymentIntent creation failed: ' . $e->getMessage(),
                ]);
                return redirect()->route('checkout.index')->with('error', 'Unable to initiate payment: ' . $e->getMessage());
            }
        }

        // --- CASHFREE PAYMENT ---
        if ($validated['payment_method'] === 'cashfree') {
            $cashfree = new CashfreeService();

            if (!$cashfree->isConfigured()) {
                return redirect()->route('checkout.index')
                    ->with('error', 'Cashfree payment gateway is not configured yet. Please contact support or select another payment method.');
            }

            try {
                $cfOrder = $cashfree->createOrder([
                    'order_id'         => $order->order_number,
                    'order_amount'     => $order->total_amount,
                    'customer_id'      => auth()->check() ? 'CUST_' . auth()->id() : 'GUEST_' . substr(md5($order->shipping_email . time()), 0, 10),
                    'customer_name'    => $order->shipping_name,
                    'customer_email'   => $order->shipping_email,
                    'customer_phone'   => $order->shipping_phone,
                    'return_url'       => route('checkout.cashfree.callback') . '?order_id={order_id}',
                    'notify_url'       => route('webhook.cashfree'),
                    'order_note'       => 'Mahadev Tractor Order #' . $order->order_number,
                ]);

                $paymentSessionId = $cfOrder['payment_session_id'] ?? null;
                $cfOrderId        = $cfOrder['cf_order_id'] ?? ($cfOrder['order_id'] ?? null);

                $order->update([
                    'cashfree_order_id'           => $cfOrderId,
                    'cashfree_payment_session_id' => $paymentSessionId,
                ]);

                return view('frontend.cashfree_payment', [
                    'order'            => $order,
                    'paymentSessionId' => $paymentSessionId,
                    'cashfreeMode'     => $cashfree->getMode(),
                ]);

            } catch (\Exception $e) {
                Log::error('Cashfree order initiation exception: ' . $e->getMessage());
                $order->update([
                    'status' => 'failed',
                    'notes'  => 'Cashfree Order creation failed: ' . $e->getMessage(),
                ]);
                return redirect()->route('checkout.index')->with('error', 'Unable to initiate Cashfree payment: ' . $e->getMessage());
            }
        }

        // --- COD FLOW ---
        session()->forget('cart');
        return redirect()->route('checkout.success', ['order_number' => $order->order_number])
            ->with('success', 'Thank you! Your order has been placed successfully.');
    }

    // Handle Stripe payment success callback (return_url)
    public function handleStripeCallback(Request $request)
    {
        $paymentIntentId = $request->query('payment_intent');
        $redirectStatus  = $request->query('redirect_status');

        if (!$paymentIntentId) {
            return redirect()->route('checkout.index')->with('error', 'Invalid payment response.');
        }

        $order = Order::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (!$order) {
            return redirect()->route('checkout.index')->with('error', 'Order not found.');
        }

        if ($redirectStatus === 'succeeded') {
            $order->update([
                'payment_status' => 'completed',
                'status'         => 'processing',
            ]);

            session()->forget('cart');

            return redirect()->route('checkout.success', ['order_number' => $order->order_number])
                ->with('success', 'Payment successful! Your order has been placed.');
        }

        // Payment failed/cancelled — restore cart and cancel order
        $this->restoreCartAndCancelOrder($order, 'Payment failed or was cancelled.');

        return redirect()->route('checkout.index')->with('error', 'Payment was not completed. Your cart has been restored.');
    }

    // Cancel payment — restore cart
    public function cancelStripePayment(Request $request)
    {
        $orderId = $request->query('order_id');
        if ($orderId) {
            $order = Order::where('id', $orderId)->where('payment_status', 'pending')->with('items.product')->first();
            if ($order) {
                $this->restoreCartAndCancelOrder($order, 'Payment cancelled by user.');
            }
        }

        return redirect()->route('checkout.index')->with('warning', 'Payment was cancelled. Your cart has been restored — please try again.');
    }

    // Stripe Webhook — server-to-server event
    public function stripeWebhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe Webhook: Invalid signature — ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::warning('Stripe Webhook: Parse error — ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 400);
        }

        Log::info('Stripe Webhook received: ' . $event->type);

        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;
            $order  = Order::where('stripe_payment_intent_id', $intent->id)->first();
            if ($order && $order->payment_status !== 'completed') {
                $order->update([
                    'payment_status' => 'completed',
                    'status'         => 'processing',
                ]);
                Log::info('Stripe Webhook: Order ' . $order->order_number . ' marked completed.');
            }
        }

        if ($event->type === 'payment_intent.payment_failed') {
            $intent = $event->data->object;
            $order  = Order::where('stripe_payment_intent_id', $intent->id)->first();
            if ($order && $order->payment_status === 'pending') {
                $order->update([
                    'payment_status' => 'failed',
                    'status'         => 'failed',
                    'notes'          => 'Stripe payment failed via webhook.',
                ]);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    // Handle Cashfree payment success/failure callback (return_url)
    public function handleCashfreeCallback(Request $request)
    {
        $orderNumber = $request->query('order_id');

        if (!$orderNumber) {
            return redirect()->route('checkout.index')->with('error', 'Invalid payment response from Cashfree.');
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return redirect()->route('checkout.index')->with('error', 'Order not found.');
        }

        // If already marked completed by webhook, redirect directly to success
        if ($order->payment_status === 'completed') {
            session()->forget('cart');
            return redirect()->route('checkout.success', ['order_number' => $order->order_number])
                ->with('success', 'Payment successful! Your order has been placed.');
        }

        try {
            $cashfree = new CashfreeService();
            $cfOrder = $cashfree->getOrder($orderNumber);
            $orderStatus = strtoupper($cfOrder['order_status'] ?? '');

            // Fetch payment details to store payment ID
            $payments = $cashfree->getOrderPayments($orderNumber);
            $latestPayment = !empty($payments) ? end($payments) : null;
            $paymentId = $latestPayment['cf_payment_id'] ?? null;

            if ($orderStatus === 'PAID') {
                $order->update([
                    'payment_status'      => 'completed',
                    'status'              => 'processing',
                    'cashfree_payment_id' => $paymentId ? (string) $paymentId : $order->cashfree_payment_id,
                ]);

                session()->forget('cart');

                return redirect()->route('checkout.success', ['order_number' => $order->order_number])
                    ->with('success', 'Payment successful! Your order has been placed.');
            }

            if (in_array($orderStatus, ['EXPIRED', 'FAILED', 'CANCELLED'])) {
                $this->restoreCartAndCancelOrder($order, 'Cashfree payment status: ' . $orderStatus);
                return redirect()->route('checkout.index')->with('error', 'Payment was not completed (' . $orderStatus . '). Your cart has been restored.');
            }

            // Still active or user returned early
            return redirect()->route('checkout.index')
                ->with('warning', 'Payment is currently in status: ' . $orderStatus . '. If amount was deducted, your order will update shortly.');

        } catch (\Exception $e) {
            Log::error('Cashfree callback verification error: ' . $e->getMessage());
            return redirect()->route('checkout.index')->with('error', 'Failed to verify payment status: ' . $e->getMessage());
        }
    }

    // Cancel Cashfree payment — restore cart
    public function cancelCashfreePayment(Request $request)
    {
        $orderId = $request->query('order_id');
        if ($orderId) {
            $order = Order::where('id', $orderId)->where('payment_status', 'pending')->with('items.product')->first();
            if ($order) {
                $this->restoreCartAndCancelOrder($order, 'Cashfree payment cancelled by customer.');
            }
        }

        return redirect()->route('checkout.index')->with('warning', 'Payment was cancelled. Your cart has been restored.');
    }

    // Cashfree Webhook — asynchronous payment notification
    public function cashfreeWebhook(Request $request)
    {
        // Respond 200 OK immediately for GET/head ping or testing checks
        if ($request->isMethod('get') || $request->isMethod('head')) {
            return response()->json(['status' => 'ok', 'message' => 'Cashfree webhook endpoint is active and listening.'], 200);
        }

        $rawPayload = $request->getContent();
        $signature  = $request->header('x-webhook-signature');
        $timestamp  = $request->header('x-webhook-timestamp');

        $data = $request->json()->all();
        $eventType = $data['type'] ?? '';

        // Cashfree dashboard test ping
        if ($eventType === 'TEST_NOTIFICATION') {
            return response()->json(['status' => 'ok', 'message' => 'Test notification received successfully.'], 200);
        }

        $cashfree = new CashfreeService();

        if ($signature && $timestamp) {
            if (!$cashfree->verifyWebhookSignature($rawPayload, $timestamp, $signature)) {
                Log::warning('Cashfree Webhook: Invalid signature');
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        Log::info('Cashfree Webhook received: ' . $eventType, ['data' => $data]);

        $orderData = $data['data']['order'] ?? [];
        $orderId = $orderData['order_id'] ?? null;
        $paymentData = $data['data']['payment'] ?? [];
        $paymentStatus = strtoupper($paymentData['payment_status'] ?? '');
        $cfPaymentId = $paymentData['cf_payment_id'] ?? null;

        if ($orderId) {
            $order = Order::where('order_number', $orderId)->first();

            if ($order) {
                if ($eventType === 'PAYMENT_SUCCESS_WEBHOOK' || $paymentStatus === 'SUCCESS') {
                    if ($order->payment_status !== 'completed') {
                        $order->update([
                            'payment_status'      => 'completed',
                            'status'              => 'processing',
                            'cashfree_payment_id' => $cfPaymentId ? (string) $cfPaymentId : $order->cashfree_payment_id,
                        ]);
                        Log::info('Cashfree Webhook: Order ' . $order->order_number . ' marked as completed.');
                    }
                } elseif ($eventType === 'PAYMENT_FAILED_WEBHOOK' || $paymentStatus === 'FAILED') {
                    if ($order->payment_status === 'pending') {
                        $order->update([
                            'payment_status' => 'failed',
                            'status'         => 'failed',
                            'notes'          => 'Cashfree payment failed via webhook: ' . ($paymentData['payment_message'] ?? 'Failed'),
                        ]);
                    }
                }
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    // Order Success page
    public function success($order_number)
    {
        $query = Order::where('order_number', $order_number)->with('items');
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        }
        $order = $query->firstOrFail();
        return view('frontend.order_success', compact('order'));
    }

    // Helper: restore session cart from order items and cancel the order
    private function restoreCartAndCancelOrder(Order $order, string $reason = ''): void
    {
        $cart = session()->get('cart', []);
        foreach ($order->items as $item) {
            $cart[$item->product_id] = [
                'name'     => $item->product_name,
                'price'    => $item->unit_price,
                'quantity' => $item->quantity,
                'image'    => $item->product ? $item->product->primary_image_url : asset('images/logo.jpeg'),
            ];
            // Restore stock
            if ($item->product) {
                $item->product->increment('quantity', $item->quantity);
            }
        }
        session()->put('cart', $cart);

        $order->update([
            'status'         => 'cancelled',
            'payment_status' => 'failed',
            'notes'          => $reason,
        ]);
    }
}
