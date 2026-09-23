<!-- ===================== MOBILE BOTTOM NAV ===================== -->
<nav class="pl-bottom-nav d-lg-none">
  <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-house-door-fill"></i>Home</a>
  <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}"><i class="bi bi-grid-3x3-gap-fill"></i>Shop</a>
  <a href="{{ route('wishlist.index') }}" class="{{ request()->routeIs('wishlist.index') ? 'active' : '' }}" style="position: relative;">
    <i class="bi bi-heart-fill"></i>Wishlist
    <span class="pl-cart-dot bg-danger" data-wishlist-badge style="{{ session()->has('wishlist') && count(session('wishlist')) > 0 ? 'display:flex;' : 'display:none;' }}">{{ session()->has('wishlist') ? count(session('wishlist')) : 0 }}</span>
  </a>
  <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i>Account</a>
  <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.index') ? 'active' : '' }}" style="position: relative;">
    <i class="bi bi-cart3"></i>Cart
    <span class="pl-cart-dot" data-cart-badge style="{{ session()->has('cart') && array_sum(array_column(session('cart'), 'quantity')) > 0 ? 'display:flex;' : 'display:none;' }}">{{ session()->has('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}</span>
  </a>
</nav>

@php
  $rawPhone = preg_replace('/[^0-9]/', '', \App\Models\Setting::get('site_phone', '919915978757'));
  if (strlen($rawPhone) == 10) {
      $rawPhone = '91' . $rawPhone;
  }
@endphp
