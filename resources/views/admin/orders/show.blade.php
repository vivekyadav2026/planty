@extends('layouts.admin')

@section('header_title', 'Order Details')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="font-serif font-bold text-slate-800 text-lg">Order: {{ $order->order_number }}</h3>
            <span class="text-xs text-slate-450 mt-1 block">Placed on: {{ $order->created_at->format('M d, Y g:i A') }}</span>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-600 flex items-center space-x-1">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Orders</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Columns: Items & Address -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Ordered Items Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
                <h4 class="font-serif font-bold text-slate-800 text-base mb-4 pb-2 border-b border-slate-50">Line Items</h4>
                <div class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between py-4 first:pt-0 last:pb-0">
                            <div class="flex items-center space-x-4">
                                @php
                                    $product = $item->product;
                                    $image = $product ? $product->primary_image_url : 'https://images.unsplash.com/photo-1416879598555-2591605c48b2?q=80&w=400&auto=format&fit=crop';
                                @endphp
                                <img src="{{ $image }}" alt="{{ $item->product_name }}" class="h-14 w-14 rounded-xl object-contain bg-slate-50 border border-slate-100 p-1 flex-shrink-0">
                                <div>
                                    <span class="block font-semibold text-slate-800">{{ $item->product_name }}</span>
                                    <span class="block text-xs text-slate-400 mt-1">Quantity: {{ $item->quantity }} @ &#8377;{{ number_format($item->unit_price, 2) }}</span>
                                </div>
                            </div>
                            <span class="font-bold text-slate-900">&#8377;{{ number_format($item->total_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6 pt-4 border-t border-slate-100 space-y-2">
                    @php
                        $subtotal = $order->items->sum('total_price');
                        $deliveryCharge = $order->delivery_charge ?? max(0, $order->total_amount - $subtotal);
                    @endphp
                    <div class="flex justify-between items-center text-xs text-slate-500 font-medium">
                        <span>Items Subtotal</span>
                        <span class="font-semibold text-slate-800">&#8377;{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-slate-500 font-medium">
                        <span>Delivery Charge</span>
                        <span class="font-semibold {{ $deliveryCharge > 0 ? 'text-slate-800' : 'text-emerald-600 font-bold' }}">
                            {{ $deliveryCharge > 0 ? '₹' . number_format($deliveryCharge, 2) : 'FREE' }}
                        </span>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-700">Order Total</span>
                        <span class="text-lg font-bold text-slate-950">&#8377;{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Shipping Information Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
                <h4 class="font-serif font-bold text-slate-800 text-base mb-4 pb-2 border-b border-slate-50 flex items-center justify-between">
                    <span>{{ $order->delivery_type === 'self_pickup' ? 'Pickup Details' : 'Shipping Details' }}</span>
                    <span class="text-xs uppercase font-bold px-2 py-0.5 rounded-full {{ $order->delivery_type === 'self_pickup' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                        {{ $order->delivery_type === 'self_pickup' ? 'Self Pickup' : 'Online Delivery' }}
                    </span>
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-slate-650">
                    @if($order->delivery_type !== 'self_pickup')
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Recipient Name</span>
                            <span class="block font-semibold text-slate-800 mt-1">{{ $order->shipping_name }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Contact Number</span>
                            <span class="block font-semibold text-slate-800 mt-1">{{ $order->shipping_phone }}</span>
                        </div>
                    @endif
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Email Address</span>
                        <span class="block font-semibold text-slate-800 mt-1">{{ $order->shipping_email }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">{{ $order->delivery_type === 'self_pickup' ? 'Warehouse Pickup Address' : 'Delivery Address' }}</span>
                        <span class="block font-semibold text-slate-800 mt-1 leading-relaxed">
                            {{ $order->shipping_address }},<br>
                            {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_zip }}
                        </span>
                    </div>
                </div>
                @if($order->driving_license || $order->sales_tax_permit)
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2">B2B Customer Documents</span>
                        <div class="flex flex-wrap gap-3">
                            @if($order->driving_license)
                                <a href="{{ asset($order->driving_license) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-xl transition shadow-3xs">
                                    <i class="fa-solid fa-id-card text-slate-500"></i> View Driving License
                                </a>
                            @endif
                            @if($order->sales_tax_permit)
                                <a href="{{ asset($order->sales_tax_permit) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-xl transition shadow-3xs">
                                    <i class="fa-solid fa-file-invoice-dollar text-slate-500"></i> View Sales Tax Permit
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
                @if($order->notes)
                    <div class="mt-6 p-4 bg-slate-50 border border-slate-100 rounded-xl text-xs leading-relaxed text-slate-500">
                        <span class="font-bold text-slate-600 block mb-1">Customer Order Notes:</span>
                        "{{ $order->notes }}"
                    </div>
                @endif
            </div>

        </div>

        <!-- Right 1 Column: Manage Status -->
        <div class="space-y-6">
            
            <!-- Update Order Status Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6">
                <h4 class="font-serif font-bold text-slate-800 text-base mb-4 pb-2 border-b border-slate-50">Update Status</h4>
                
                <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <!-- Order Status -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Order Status</label>
                        <select name="status" class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
                            <option value="pending" @selected($order->status === 'pending')>Pending</option>
                            <option value="processing" @selected($order->status === 'processing')>Processing</option>
                            <option value="shipped" @selected($order->status === 'shipped')>Shipped</option>
                            <option value="completed" @selected($order->status === 'completed')>Completed</option>
                            <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                            <option value="failed" @selected($order->status === 'failed')>Failed</option>
                            <option value="returned" @selected($order->status === 'returned')>Returned (B2B)</option>
                            <option value="return_rejected" @selected($order->status === 'return_rejected')>Return Rejected (B2B)</option>
                        </select>
                    </div>

                    <!-- Payment Status -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Payment Status</label>
                        <select name="payment_status" class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
                            <option value="pending" @selected($order->payment_status === 'pending')>Pending</option>
                            <option value="completed" @selected($order->payment_status === 'completed')>Paid (Completed)</option>
                            <option value="failed" @selected($order->payment_status === 'failed')>Failed</option>
                        </select>
                    </div>

                    <!-- Shiprocket Courier & Tracking Details -->
                    @if($order->delivery_type === 'online_delivery')
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Shiprocket AWB Code</label>
                            <input type="text" name="shiprocket_awb_code" value="{{ old('shiprocket_awb_code', $order->shiprocket_awb_code) }}" 
                                   placeholder="e.g. 143245678912"
                                   class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white font-mono">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Assigned Courier Name</label>
                            <input type="text" name="shiprocket_courier_name" value="{{ old('shiprocket_courier_name', $order->shiprocket_courier_name) }}" 
                                   placeholder="e.g. Delhivery, BlueDart, DTDC"
                                   class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
                        </div>
                    @endif

                    <!-- Refund Details (if any) -->
                    @if(!empty($order->refund_account_details))
                        <div class="space-y-1.5 pt-3 border-t border-slate-150">
                            <label class="text-xs font-bold text-rose-600 uppercase tracking-wider block">Customer Refund Details</label>
                            <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 text-sm text-slate-800 whitespace-pre-wrap font-medium">
                                {{ $order->refund_account_details }}
                            </div>
                        </div>
                    @endif

                    <!-- Return Request Processing (Only shown if return has been requested) -->
                    @if($order->return_status !== null)
                        <div class="space-y-1.5 pt-3 border-t border-slate-150">
                            <label class="text-xs font-bold text-red-600 uppercase tracking-wider block">B2B Return/Replace Request Status</label>
                            <select name="return_status" class="w-full border border-red-200 focus:ring-1 focus:ring-red-500 focus:border-red-500 rounded-xl text-sm px-4 py-2.5 bg-white font-bold text-red-700">
                                <option value="pending" @selected($order->return_status === 'pending')>Pending Review</option>
                                <option value="approved" @selected($order->return_status === 'approved')>Approve Request</option>
                                <option value="rejected" @selected($order->return_status === 'rejected')>Reject Request</option>
                            </select>
                            
                            <div class="bg-red-50/30 border border-red-150 rounded-xl p-3.5 mt-2 space-y-2 text-xs">
                                <div>
                                    <span class="block text-[9px] uppercase font-bold text-red-500">Request Type</span>
                                    <span class="block font-semibold text-slate-800 mt-0.5">{{ ucfirst($order->return_type ?? 'Return') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] uppercase font-bold text-red-500">Reason</span>
                                    <span class="block font-semibold text-slate-800 mt-0.5">{{ $order->return_reason }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] uppercase font-bold text-red-500">Customer Comments</span>
                                    <p class="text-slate-650 font-medium leading-normal mt-0.5">{{ $order->return_comments }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="w-full bg-[#C49A6C] hover:bg-[#b0875b] text-white font-bold text-sm py-3 rounded-xl transition cursor-pointer shadow-md shadow-[#C49A6C]/25">
                        Update Details
                    </button>                </form>
            </div>

            <!-- Shiprocket Shipping & Fulfillment Card -->
            @if($order->delivery_type === 'online_delivery')
                <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6 text-sm text-slate-650 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-50">
                        <h4 class="font-serif font-bold text-slate-800 text-base m-0">Shiprocket Delivery</h4>
                        <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full {{ $order->shiprocket_status ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-500' }}">
                            {{ $order->shiprocket_status ?: 'Unfulfilled' }}
                        </span>
                    </div>

                    @if($order->shiprocket_shipment_id || $order->shiprocket_order_id)
                        <div class="space-y-3 bg-slate-50 p-3.5 rounded-xl border border-slate-100 text-xs">
                            @if($order->shiprocket_courier_name)
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Courier Partner</span>
                                    <span class="block font-bold text-slate-800">{{ $order->shiprocket_courier_name }}</span>
                                </div>
                            @endif

                            @if($order->shiprocket_awb_code)
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">AWB Tracking Code</span>
                                    <span class="block font-bold text-slate-900 font-mono text-sm select-all">{{ $order->shiprocket_awb_code }}</span>
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                                <div>
                                    <span class="text-slate-400">Shipment ID:</span>
                                    <span class="font-mono font-bold text-slate-700">{{ $order->shiprocket_shipment_id ?: 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400">Order ID:</span>
                                    <span class="font-mono font-bold text-slate-700">{{ $order->shiprocket_order_id ?: 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2 pt-1">
                            @if(!$order->shiprocket_awb_code)
                                 <!-- Step 2: Assign AWB Code -->
                                <form action="{{ route('admin.orders.shiprocket.awb', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            style="background: #f08038; color: #ffffff;"
                                            class="w-full text-white font-bold text-xs py-2.5 rounded-xl transition cursor-pointer flex items-center justify-center gap-1.5 shadow-sm hover:opacity-95">
                                        <i class="fa-solid fa-barcode"></i> Assign Courier & Generate AWB
                                    </button>
                                </form>
                            @else
                                <!-- Step 3: Track & Print Label -->
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('admin.orders.shiprocket.track', $order->id) }}" class="text-center bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs py-2.5 rounded-xl transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-location-crosshairs text-[11px]"></i> Track Live
                                    </a>
                                    <a href="{{ route('admin.orders.shiprocket.label', $order->id) }}" target="_blank" class="text-center bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2.5 rounded-xl transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-print text-[11px]"></i> Print Label
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Step 1: Create Shipment in Shiprocket (Admin Manual Trigger) -->
                        <div class="text-center py-2 space-y-3">
                            <p class="text-xs text-slate-500 m-0">This order is ready to be sent to Shiprocket. Click below to generate the shipment.</p>
                            <form action="{{ route('admin.orders.shiprocket.create', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        style="background: #f08038; color: #ffffff;"
                                        class="w-full text-white font-bold text-xs py-3 rounded-xl transition cursor-pointer flex items-center justify-center gap-2 shadow-md hover:opacity-95">
                                    <i class="fa-solid fa-truck-fast"></i>
                                    <span>Send Order to Shiprocket</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif


            <!-- Transaction Information Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs p-6 text-sm text-slate-650 space-y-4">
                <h4 class="font-serif font-bold text-slate-800 text-base pb-2 border-b border-slate-50">Payment Info</h4>
                
                <div>
                    <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Method</span>
                    <span class="block font-semibold text-slate-800 mt-1 uppercase">{{ $order->payment_method }}</span>
                </div>

                @if($order->payment_method === 'cashfree')
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Cashfree Payment ID</span>
                        <span class="block font-semibold text-slate-800 mt-1 font-mono text-xs select-all">{{ $order->cashfree_payment_id ?: 'Pending / Unpaid' }}</span>
                    </div>
                    @if($order->cashfree_order_id)
                    <div class="mt-2">
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Cashfree Order ID</span>
                        <span class="block font-semibold text-slate-800 mt-0.5 font-mono text-xs select-all">{{ $order->cashfree_order_id }}</span>
                    </div>
                    @endif
                @elseif($order->payment_method === 'stripe')
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Stripe Payment Intent</span>
                        <span class="block font-semibold text-slate-800 mt-1 font-mono text-xs select-all">{{ $order->stripe_payment_intent_id ?: 'N/A' }}</span>
                    </div>
                @else
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">COD Information</span>
                        <span class="block text-slate-500 mt-1 leading-relaxed">Collect cash upon delivery. Collect exactly &#8377;{{ number_format($order->total_amount, 2) }}.</span>
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
