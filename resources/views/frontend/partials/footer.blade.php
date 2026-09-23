<footer style="background-color: var(--primary-green, #1B4332); color: #D8F3DC; font-family: 'Outfit', sans-serif; padding-top: 4rem; padding-bottom: 2rem;">
  <div class="container-fluid px-4 px-xl-5">
    <div class="row g-4">
      
      <!-- Brand & Info -->
      <div class="col-lg-4 mb-4">
        <a href="{{ route('home') }}" class="d-inline-flex align-items-center mb-3 text-decoration-none" style="color: #fff; font-size: 1.8rem; font-weight: 800;">
          <i class="bi bi-flower1 me-2" style="color: var(--light-green, #D8F3DC);"></i> Urban Nursery
        </a>
        <p style="font-size: 0.95rem; color: #a3c4b0; line-height: 1.6; max-width: 350px;">
          Urban Nursery is your trusted nursery for premium indoor and outdoor plants. We bring nature to your doorstep with love and care.
        </p>
        <div class="d-flex gap-3 mt-4">
          <a href="#" class="text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(255,255,255,0.1); border-radius: 50%; transition: background 0.3s;">
            <i class="bi bi-facebook"></i>
          </a>
          <a href="#" class="text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(255,255,255,0.1); border-radius: 50%; transition: background 0.3s;">
            <i class="bi bi-instagram"></i>
          </a>
          <a href="#" class="text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(255,255,255,0.1); border-radius: 50%; transition: background 0.3s;">
            <i class="bi bi-youtube"></i>
          </a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-lg-2 col-md-4 mb-4">
        <h5 class="mb-4" style="color: #fff; font-weight: 700;">Quick Links</h5>
        <ul class="list-unstyled d-flex flex-column gap-2">
          <li><a href="{{ route('home') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Home</a></li>
          <li><a href="{{ route('shop') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Shop</a></li>
          <li><a href="{{ route('about') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">About Us</a></li>
          <li><a href="{{ route('contact') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Contact Us</a></li>
          <li><a href="#" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Blog</a></li>
        </ul>
      </div>

      <!-- Shop By -->
      <div class="col-lg-2 col-md-4 mb-4">
        <h5 class="mb-4" style="color: #fff; font-weight: 700;">Shop By</h5>
        <ul class="list-unstyled d-flex flex-column gap-2">
          <li><a href="{{ url('/shop?cat=indoor-plants') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Indoor Plants</a></li>
          <li><a href="{{ url('/shop?cat=outdoor-plants') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Outdoor Plants</a></li>
          <li><a href="{{ url('/shop?cat=seeds') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Seeds & Bulbs</a></li>
          <li><a href="{{ url('/shop?cat=pots') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Pots & Planters</a></li>
          <li><a href="{{ url('/shop?cat=succulents') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Succulents</a></li>
        </ul>
      </div>

      <!-- Support / Legal -->
      <div class="col-lg-4 col-md-4 mb-4">
        <h5 class="mb-4" style="color: #fff; font-weight: 700;">Support & Policy</h5>
        <ul class="list-unstyled d-flex flex-column gap-2 mb-4">
          <li><a href="{{ route('terms') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Terms & Conditions</a></li>
          <li><a href="{{ route('privacy') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Privacy Policy</a></li>
          <li><a href="{{ route('refund') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Refund Policy</a></li>
          <li><a href="{{ route('shipping') }}" class="text-decoration-none" style="color: #a3c4b0; transition: color 0.3s;">Shipping Policy</a></li>
        </ul>
        <div class="d-flex align-items-center gap-2" style="color: #a3c4b0;">
          <i class="bi bi-envelope"></i> support@urbannursery.com
        </div>
      </div>

    </div>

    <hr style="border-color: rgba(255,255,255,0.1); margin: 2rem 0;">

    <div class="row align-items-center">
      <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
        <p class="mb-0" style="color: #a3c4b0; font-size: 0.9rem;">&copy; {{ date('Y') }} Urban Nursery. All Rights Reserved.</p>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <!-- Payment Icons Placeholder -->
        <div class="d-inline-flex gap-2 bg-white rounded px-3 py-2">
          <i class="bi bi-credit-card-fill text-dark fs-5"></i>
          <span class="text-dark fw-bold fs-6">UPI</span>
          <i class="bi bi-paypal text-primary fs-5"></i>
        </div>
      </div>
    </div>
  </div>
</footer>

<style>
  footer a:hover {
    color: #fff !important;
  }
</style>
