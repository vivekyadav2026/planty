@extends('layouts.admin')

@section('header_title', 'Global Settings')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-xs">
    
    <div class="mb-6 flex items-center justify-between">
        <h3 class="font-serif font-bold text-slate-800 text-lg">System Settings</h3>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-xmark text-rose-600 text-sm"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        <!-- Site Identity -->
        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 space-y-4">
            <h4 class="font-serif font-bold text-slate-800 text-sm pb-1.5 border-b border-slate-200">Shop Identity</h4>

            <!-- Site Name -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Shop Name</label>
                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'Urban Nursery' }}" placeholder="Urban Nursery"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <!-- Site Email -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Support Email</label>
                <input type="email" name="site_email" value="{{ $settings['site_email'] ?? 'support@urbannursery.com' }}" placeholder="support@urbannursery.com"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <!-- Site Phone -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Support Phone</label>
                <input type="text" name="site_phone" value="{{ $settings['site_phone'] ?? '+1-800-555-0199' }}" placeholder="+1-800-555-0199"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <!-- Site Address -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Physical Address</label>
                <textarea name="site_address" rows="3" placeholder="Shop Address..."
                          class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">{{ $settings['site_address'] ?? '12800 Northborough Dr, Houston, TX 77067' }}</textarea>
            </div>
        </div>

        <!-- Cashfree Payment Gateway Settings -->
        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-1.5 border-b border-slate-200">
                <h4 class="font-serif font-bold text-slate-800 text-sm">Cashfree Payment Gateway (India - UPI / Cards / NetBanking)</h4>
                <span class="text-[10px] uppercase font-extrabold bg-primary/10 text-primary px-2 py-0.5 rounded-full">Recommended</span>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Environment / Mode</label>
                <select name="cashfree_mode" class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
                    <option value="sandbox" {{ ($settings['cashfree_mode'] ?? config('services.cashfree.mode', 'sandbox')) === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                    <option value="production" {{ ($settings['cashfree_mode'] ?? config('services.cashfree.mode')) === 'production' ? 'selected' : '' }}>Production (Live)</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Cashfree App ID (Client ID)</label>
                <input type="text" name="cashfree_app_id" value="{{ $settings['cashfree_app_id'] ?? config('services.cashfree.app_id', '') }}" placeholder="e.g. TEST1038593..."
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <div class="space-y-1.5" x-data="{ showSecret: false }">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Cashfree Secret Key</label>
                <div class="relative">
                    <input :type="showSecret ? 'text' : 'password'" name="cashfree_secret_key" value="{{ $settings['cashfree_secret_key'] ?? config('services.cashfree.secret_key', '') }}" placeholder="e.g. cfsk_ma_test_..."
                           class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm pl-4 pr-10 py-2.5 bg-white">
                    <button type="button" @click="showSecret = !showSecret" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-[#C49A6C] focus:outline-none cursor-pointer">
                        <i class="fa-solid text-sm" :class="showSecret ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/60 text-xs text-amber-800 space-y-1">
                <p class="font-bold"><i class="fa-solid fa-circle-info me-1"></i> Cashfree Webhook URL:</p>
                <code class="block bg-white p-2 rounded border border-amber-200 font-mono text-[11px] select-all break-all">{{ url('/webhook/cashfree') }}</code>
                <p class="text-[10px] text-amber-700">Add this URL in your Cashfree Merchant Dashboard under <em>Developers > Webhooks</em>.</p>
            </div>
        </div>

        <!-- Social Media Settings -->
        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 space-y-4">
            <h4 class="font-serif font-bold text-slate-800 text-sm pb-1.5 border-b border-slate-200">Social Media Links</h4>

            <!-- Facebook -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Facebook URL</label>
                <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/yourpage"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <!-- Twitter / X -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Twitter / X URL</label>
                <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}" placeholder="https://x.com/yourhandle"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <!-- Instagram -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Instagram URL</label>
                <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/yourhandle"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <!-- LinkedIn -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">LinkedIn URL</label>
                <input type="url" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}" placeholder="https://linkedin.com/company/yourcompany"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <!-- YouTube -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">YouTube URL</label>
                <input type="url" name="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/c/yourchannel"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>
        </div>

        <!-- Shiprocket Shipping Settings -->
        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 space-y-4">
            <div class="flex items-center justify-between pb-1.5 border-b border-slate-200">
                <h4 class="font-serif font-bold text-slate-800 text-sm">Shiprocket Shipping & Delivery (India)</h4>
                <span class="text-[10px] uppercase font-extrabold bg-blue-50 text-blue-600 border border-blue-200/50 px-2 py-0.5 rounded-full">Courier Partner</span>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Shiprocket Account Email</label>
                <input type="email" name="shiprocket_email" value="{{ $settings['shiprocket_email'] ?? '' }}" placeholder="your-shiprocket-login@email.com"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
            </div>

            <div class="space-y-1.5" x-data="{ showSecret: false }">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Shiprocket Account Password</label>
                <div class="relative">
                    <input :type="showSecret ? 'text' : 'password'" name="shiprocket_password" value="{{ $settings['shiprocket_password'] ?? '' }}" placeholder="••••••••••••••••"
                           class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm pl-4 pr-10 py-2.5 bg-white">
                    <button type="button" @click="showSecret = !showSecret" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-[#C49A6C] focus:outline-none cursor-pointer">
                        <i class="fa-solid text-sm" :class="showSecret ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Pickup Location Nickname</label>
                <input type="text" name="shiprocket_pickup_location" value="{{ $settings['shiprocket_pickup_location'] ?? 'Primary' }}" placeholder="Primary"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white">
                <span class="text-[10px] text-slate-400 block">Must match the exact 'Pickup Location' nickname created in your Shiprocket panel under <em>Settings &gt; Pickup Addresses</em> (e.g. Primary, Warehouse).</span>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Pickup / Origin Pincode (6-digit)</label>
                <input type="text" name="shiprocket_pickup_pincode" value="{{ $settings['shiprocket_pickup_pincode'] ?? '' }}" placeholder="e.g. 124001" maxlength="6"
                       class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white font-mono">
                <span class="text-[10px] text-slate-400 block">Pincode of your shop/warehouse location where Shiprocket courier will come to pick up parcels.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-200/80">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Default Delivery Charge (&#8377;)</label>
                    <input type="number" step="0.01" min="0" name="default_delivery_charge" value="{{ $settings['default_delivery_charge'] ?? '99' }}" placeholder="99"
                           class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white font-mono">
                    <span class="text-[10px] text-slate-400 block">Fixed delivery charge if live rate is unavailable or before pincode is entered (0 for Free).</span>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Free Delivery on Orders Above (&#8377;)</label>
                    <input type="number" step="0.01" min="0" name="free_shipping_threshold" value="{{ $settings['free_shipping_threshold'] ?? '' }}" placeholder="e.g. 1999 (Leave empty for no free delivery)"
                           class="w-full border border-slate-200 focus:ring-1 focus:ring-[#C49A6C] focus:border-[#C49A6C] rounded-xl text-sm px-4 py-2.5 bg-white font-mono">
                    <span class="text-[10px] text-slate-400 block">Orders with cart value above this amount will automatically get Free Delivery.</span>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end pt-4 border-t border-slate-100">
            <button type="submit" class="w-full sm:w-auto bg-[#C49A6C] hover:bg-[#b0875b] text-white font-bold text-sm px-8 py-3.5 rounded-xl shadow-md shadow-[#C49A6C]/20 transition cursor-pointer">
                Save Settings
            </button>
        </div>
    </form>

</div>
@endsection
