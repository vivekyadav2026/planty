<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureOrderColumnsExist();

        $query = Order::with('user');

        if ($request->filled('status')) {
            if ($request->status === 'return_requested') {
                $query->where('return_status', 'pending');
            } elseif ($request->status === 'returned') {
                $query->where(function($q) {
                    $q->where('status', 'returned')
                      ->orWhere('return_status', 'approved');
                });
            } elseif ($request->status === 'return_rejected') {
                $query->where(function($q) {
                    $q->where('status', 'return_rejected')
                      ->orWhere('return_status', 'rejected');
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('shipping_name', 'like', '%' . $request->search . '%')
                  ->orWhere('shipping_email', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->ensureOrderColumnsExist();
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status'                  => 'required|string|in:pending,processing,shipped,completed,cancelled,failed,returned,return_rejected',
            'payment_status'          => 'required|string|in:pending,completed,failed',
            'shiprocket_awb_code'     => 'nullable|string|max:100',
            'shiprocket_courier_name' => 'nullable|string|max:100',
            'return_status'           => 'nullable|string|in:pending,approved,rejected',
        ]);

        $status = $request->status;
        $returnStatus = $request->filled('return_status') ? $request->return_status : $order->return_status;

        // Auto-sync order status with return status updates
        if ($request->filled('return_status')) {
            if ($request->return_status === 'approved') {
                $status = 'returned';
            } elseif ($request->return_status === 'rejected') {
                $status = 'return_rejected';
            }
        }

        $order->update([
            'status'                  => $status,
            'payment_status'          => $request->payment_status,
            'shiprocket_awb_code'     => $request->shiprocket_awb_code,
            'shiprocket_courier_name' => $request->shiprocket_courier_name,
            'return_status'           => $returnStatus,
        ]);

        return redirect()->back()->with('success', 'Order updated successfully.');
    }

    /**
     * Admin Action: Create Shipment on Shiprocket manually
     */
    public function createShiprocketShipment(Order $order, \App\Services\ShiprocketService $shiprocket)
    {
        $this->ensureOrderColumnsExist();
        try {
            $result = $shiprocket->createShipment($order);
            $shipmentId = !empty($result['shipment_id']) ? $result['shipment_id'] : ($result['order_id'] ?? 'Success');
            return redirect()->back()->with('success', 'Shiprocket shipment created successfully! Order/Shipment ID: ' . $shipmentId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Shiprocket Error: ' . $e->getMessage());
        }
    }

    /**
     * Admin Action: Generate AWB on Shiprocket manually
     */
    public function generateShiprocketAwb(Order $order, \App\Services\ShiprocketService $shiprocket)
    {
        $this->ensureOrderColumnsExist();
        try {
            $result = $shiprocket->generateAwb($order);
            return redirect()->back()->with('success', 'AWB Assigned! Courier: ' . ($result['courier_name'] ?? '') . ' | AWB: ' . ($result['awb_code'] ?? ''));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Shiprocket AWB Error: ' . $e->getMessage());
        }
    }

    /**
     * Admin Action: Track Live Shipment on Shiprocket
     */
    public function trackShiprocketShipment(Order $order, \App\Services\ShiprocketService $shiprocket)
    {
        $this->ensureOrderColumnsExist();
        try {
            $result = $shiprocket->trackShipment($order);
            return redirect()->back()->with('info', 'Live Status: ' . ($result['status'] ?? 'Active'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Shiprocket Tracking Error: ' . $e->getMessage());
        }
    }

    /**
     * Admin Action: Print Shipping Label
     */
    public function printShiprocketLabel(Order $order, \App\Services\ShiprocketService $shiprocket)
    {
        $this->ensureOrderColumnsExist();
        try {
            $labelUrl = $shiprocket->printLabel($order);
            if ($labelUrl) {
                return redirect()->away($labelUrl);
            }
            return redirect()->back()->with('error', 'Label URL not found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Shiprocket Label Error: ' . $e->getMessage());
        }
    }

    /**
     * Ensure all newly added order columns exist in database table dynamically
     */
    protected function ensureOrderColumnsExist(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
                \Illuminate\Support\Facades\Schema::table('orders', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'delivery_charge')) {
                        $table->decimal('delivery_charge', 10, 2)->default(0)->after('total_amount');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'cashfree_order_id')) {
                        $table->string('cashfree_order_id')->nullable()->after('payment_method');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'cashfree_payment_id')) {
                        $table->string('cashfree_payment_id')->nullable()->after('cashfree_order_id');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'cashfree_payment_session_id')) {
                        $table->text('cashfree_payment_session_id')->nullable()->after('cashfree_payment_id');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'shiprocket_order_id')) {
                        $table->string('shiprocket_order_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'shiprocket_shipment_id')) {
                        $table->string('shiprocket_shipment_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'shiprocket_awb_code')) {
                        $table->string('shiprocket_awb_code')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'shiprocket_courier_name')) {
                        $table->string('shiprocket_courier_name')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('orders', 'shiprocket_status')) {
                        $table->string('shiprocket_status')->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Order column auto-ensure exception: ' . $e->getMessage());
        }
    }
}
