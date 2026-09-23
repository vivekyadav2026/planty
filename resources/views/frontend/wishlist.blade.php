@extends('layouts.frontend')

@section('title', 'My Wishlist')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <!-- Breadcrumb & Title Inline -->
        <div class="mb-1 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-slate-100">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight" style="font-family: 'Outfit', sans-serif;">My Account</h1>
                <p class="text-[10px] text-slate-450 mt-1 flex items-center gap-1.5 font-bold uppercase tracking-wider">
                    <a href="/" class="hover:text-primary transition-colors">Home</a> 
                    <span class="text-slate-300">/</span> 
                    @auth
                        <a href="/dashboard" class="hover:text-primary transition-colors">Dashboard</a> 
                        <span class="text-slate-300">/</span> 
                    @endauth
                    <span class="text-slate-800">Wishlist</span>
                </p>
            </div>
        </div>

        @auth
        <div class="flex flex-col lg:flex-row gap-4">
            @include('frontend.partials.customer_sidebar')
            
            <div class="w-full lg:w-3/4">
        @else
            <div class="w-full">
        @endauth
            @if($products->count() > 0)
                <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-3 mb-3">
                    @foreach($products as $product)
                    <div class="col" data-product>
                      <div class="pl-product-card">
                        <div class="pl-product-img-wrap">
                          <!-- Tags Overlay -->
                          @if($product->sale_price)
                            <div class="pl-card-tags">
                              @php
                                $discount = round((($product->price - $product->sale_price) / $product->price) * 100);
                              @endphp
                              <span class="pl-tag pl-tag-sale">{{ $discount }}% OFF</span>
                            </div>
                          @endif
                          @if($product->quantity <= 0)
                            <div class="pl-card-tags" style="top:auto;bottom:8px;left:8px;right:auto;">
                              <span class="pl-tag" style="background:#ef4444;color:#fff;">Out of Stock</span>
                            </div>
                          @endif
                          <button class="pl-wishlist-btn" data-wishlist-product-id="{{ $product->id }}" onclick="PL.toggleWishlist('{{ $product->id }}')"><i class="bi bi-heart-fill text-danger"></i></button>
                          <a href="{{ route('product.show', $product->slug) }}"><img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: contain;"></a>
                        </div>
                        <div class="pl-product-body">
                          <a href="{{ route('product.show', $product->slug) }}" class="pl-product-title" title="{{ $product->name }}">{{ $product->name }}</a>
                          <div class="pl-product-price">&#8377;{{ number_format($product->sale_price ?? $product->price, 2) }}
                            @if($product->sale_price)
                              <span class="pl-old">&#8377;{{ number_format($product->price, 2) }}</span>
                            @endif
                          </div>
                          <div class="mt-auto d-flex gap-2">
                            @if($product->quantity > 0)
                              <button class="pl-btn-outline text-center flex-grow-1 py-2" onclick="PL.buyNow('{{ $product->id }}')">Buy Now</button>
                              <button class="btn btn-pl-primary px-3 d-flex align-items-center justify-content-center" style="border-radius:8px;" onclick="PL.addToCartById('{{ $product->id }}')"><i class="bi bi-cart-plus"></i></button>
                            @else
                              <button class="btn w-100 py-2" style="border-radius:8px;background:#f1f5f9;color:#94a3b8;border:1px solid #e2e8f0;font-size:0.75rem;font-weight:700;cursor:not-allowed;" disabled><i class="bi bi-x-circle me-1"></i> Out of Stock</button>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-[#f5faf7]/40 rounded-xl border border-dashed border-primary/20">
                    <i class="fa-regular fa-heart text-5xl text-gray-300 mb-4"></i>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight mb-1" style="font-family: 'Outfit', sans-serif;">Your Wishlist is Empty</h2>
                    <p class="text-slate-500 text-xs mb-6 max-w-xs mx-auto">Add items that you like to your wishlist so you can find them easily later.</p>
                    <a href="/shop" class="inline-block bg-primary hover:bg-primary-dark text-white font-semibold px-6 py-2.5 rounded-lg text-xs tracking-wider transition">
                        BROWSE PRODUCTS
                    </a>
                </div>
            @endif
        </div>

        @auth
        </div>
        </div>
        @endauth
    </div>
@endsection
