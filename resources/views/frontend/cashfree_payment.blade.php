@extends('layouts.frontend')

@section('title', 'Complete Payment — Urban Nursery')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-10 bg-slate-50">
    <div class="w-full max-w-md">

        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-3 shadow-md" style="background: linear-gradient(135deg, #f08038, #cc5500);">
                <i class="fa-solid fa-lock text-white text-lg"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-900" style="font-family: 'Outfit', sans-serif;">Complete Online Payment</h1>
            <p class="text-xs text-slate-500 mt-1">Pay securely via Cashfree (UPI, Cards, NetBanking, Wallets)</p>
        </div>

        {{-- Order Summary Card --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-4 shadow-sm">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Order</span>
                <span class="text-xs font-extrabold text-slate-900">#{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 font-medium">Total Payable</span>
                <span class="text-lg font-extrabold text-slate-900" style="font-family: 'Outfit', sans-serif;">&#8377;{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- Cashfree Payment Card --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-shield-halved text-emerald-500 text-sm"></i>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Accepted Payment Options</span>
                <div class="ml-auto flex items-center gap-1.5">
                    <span class="text-[10px] font-bold bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full border border-amber-200/50">UPI</span>
                    <span class="text-[10px] font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full border border-blue-200/50">Cards</span>
                    <span class="text-[10px] font-bold bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full border border-purple-200/50">NetBanking</span>
                </div>
            </div>

            <div class="text-center py-4 space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-primary/10 flex items-center justify-center text-primary text-xl">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Click the button below to open the secure Cashfree checkout. You will be redirected back automatically once payment is completed.
                </p>

                <div id="payment-error-msg" class="hidden text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg p-3 text-left">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    <span id="payment-error-text"></span>
                </div>

                <button id="pay-now-btn" type="button"
                    class="w-full text-white font-bold py-3 px-4 rounded-xl text-sm tracking-wide transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2 cursor-pointer mt-4"
                    style="background: linear-gradient(135deg, #f08038, #cc5500);">
                    <i class="fa-solid fa-lock text-xs mr-1"></i>
                    <span>Proceed to Pay &#8377;{{ number_format($order->total_amount, 2) }}</span>
                </button>
            </div>

            {{-- Cancel link --}}
            <div class="text-center mt-3 pt-3 border-t border-slate-100">
                <a href="{{ route('checkout.cashfree.cancel', ['order_id' => $order->id]) }}"
                   class="text-[11px] text-slate-400 hover:text-red-500 transition-colors font-medium"
                   onclick="return confirm('Cancel payment? Your cart will be restored.')">
                    <i class="fa-solid fa-xmark mr-1"></i> Cancel and return to cart
                </a>
            </div>
        </div>

        {{-- Security badge --}}
        <div class="flex items-center justify-center gap-2 mt-4 text-[10px] text-slate-400 font-medium">
            <i class="fa-solid fa-shield-halved text-emerald-500"></i>
            <span>Secured by <strong>Cashfree Payments</strong> · 256-bit SSL Encryption</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ $cashfreeMode === 'production' ? 'https://sdk.cashfree.com/js/v3/cashfree.js' : 'https://sdk.cashfree.com/js/v3/cashfree.sandbox.js' }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cashfree = Cashfree({
            mode: "{{ $cashfreeMode === 'production' ? 'production' : 'sandbox' }}"
        });

        const paymentSessionId = "{{ $paymentSessionId }}";
        const payBtn = document.getElementById('pay-now-btn');
        const errorBox = document.getElementById('payment-error-msg');
        const errorText = document.getElementById('payment-error-text');

        function triggerPayment() {
            if (!paymentSessionId) {
                errorBox.classList.remove('hidden');
                errorText.innerText = "Payment session could not be initialized. Please try again.";
                return;
            }

            const checkoutOptions = {
                paymentSessionId: paymentSessionId,
                redirectTarget: "_self"
            };

            cashfree.checkout(checkoutOptions).then(function (result) {
                if (result.error) {
                    console.error("Cashfree checkout error:", result.error);
                    errorBox.classList.remove('hidden');
                    errorText.innerText = result.error.message || "Payment initiation failed.";
                }
                if (result.redirect) {
                    console.log("Cashfree redirecting...");
                }
            });
        }

        payBtn.addEventListener('click', triggerPayment);

        // Auto-launch checkout on page load for seamless UX
        setTimeout(triggerPayment, 600);
    });
</script>
@endpush
