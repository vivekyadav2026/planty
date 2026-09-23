<!-- Google Fonts for Header -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
  /* Header Styles matching the image */
  .pl-top-bar {
    background-color: #274833; /* Dark green */
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 500;
    padding: 6px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .pl-middle-bar {
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #ffffff;
    border-bottom: 1px solid #f5f5f5;
  }
  
  .pl-contact-info {
    display: flex;
    gap: 20px;
    font-size: 0.95rem;
    color: #333;
    font-weight: 500;
    flex: 1;
  }
  
  .pl-logo-area {
    text-align: center;
    text-decoration: none;
    color: #111;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .pl-logo-icon {
    font-size: 2rem;
    color: #7DA948; /* Tree color */
    line-height: 1;
  }
  
  .pl-logo-text {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1;
    letter-spacing: -0.5px;
  }
  
  .pl-logo-curve {
    width: 60px;
    height: 4px;
    background: #a9d18e;
    border-radius: 50%;
    margin: 4px auto 0;
  }

  .pl-icons-area {
    display: flex;
    gap: 20px;
    align-items: center;
    flex: 1;
    justify-content: flex-end;
  }
  
  .pl-icon-btn {
    color: #333;
    font-size: 1.3rem;
    text-decoration: none;
    position: relative;
    transition: color 0.2s;
  }
  
  .pl-icon-btn:hover {
    color: #7DA948;
  }
  
  .pl-cart-badge {
    position: absolute;
    top: -6px;
    right: -10px;
    background-color: #e53935;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .pl-bottom-bar {
    display: flex;
    justify-content: center;
    padding: 12px 0;
    background-color: #ffffff;
    border-bottom: 1px solid #f0f0f0;
    gap: 30px;
    flex-wrap: wrap;
  }
  
  .pl-nav-link {
    color: #222;
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.2s;
  }
  
  .pl-nav-link:hover {
    color: #7DA948;
  }
  
  .pl-nav-link i {
    font-size: 0.75rem;
    color: #777;
  }

  /* Full Screen Search Overlay */
  .search-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.98);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
  }
  .search-overlay.active {
    opacity: 1;
    visibility: visible;
  }
  .close-search {
    position: absolute;
    top: 30px;
    right: 40px;
    font-size: 2rem;
    color: #333;
    cursor: pointer;
  }
  
  @media(max-width: 991px) {
    .pl-middle-bar { padding: 10px 15px; }
    .pl-contact-info { display: none; } /* Hide email/phone on mobile */
    .pl-bottom-bar { display: none; } /* Handled by offcanvas */
  }
</style>

<!-- Top Bar -->
<div class="pl-top-bar" id="topPromoBar">
  <div style="flex: 1; display: flex; align-items: center; gap: 20px; justify-content: center;">
    <i class="bi bi-chevron-left" style="cursor: pointer; opacity: 0.7;"></i>
    <span class="d-flex align-items-center gap-2">
      <i class="bi bi-arrow-repeat text-info"></i> 7-Day Replacement for Damaged Plants
    </span>
    <i class="bi bi-chevron-right" style="cursor: pointer; opacity: 0.7;"></i>
  </div>
  <i class="bi bi-x-lg" style="cursor: pointer; font-size: 0.8rem;" onclick="document.getElementById('topPromoBar').style.display='none'"></i>
</div>

<!-- ===================== DESKTOP HEADER (d-none d-lg-block) ===================== -->
<header class="d-none d-lg-block" style="position: sticky; top: 0; z-index: 1020; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
  
  <!-- Middle Bar (Logo & Icons) -->
  <div class="pl-middle-bar">
    <!-- Contact Info -->
    <div class="pl-contact-info">
      <span>+91 98765 43210</span>
      <span>support@urbannursery.com</span>
    </div>

    <!-- Logo -->
    <a href="{{ route('home') }}" class="pl-logo-area">
      <div class="pl-logo-icon"><i class="bi bi-flower1"></i></div>
      <div class="pl-logo-text">Urban Nursery</div>
      <div class="pl-logo-curve"></div>
    </a>

    <!-- Icons -->
    <div class="pl-icons-area">
      <a href="javascript:void(0)" class="pl-icon-btn" onclick="document.getElementById('searchOverlay').classList.add('active')">
        <i class="bi bi-search"></i>
      </a>
      
      @auth
        <a href="{{ route('dashboard') }}" class="pl-icon-btn"><i class="bi bi-person"></i></a>
      @else
        <a href="{{ route('login') }}" class="pl-icon-btn"><i class="bi bi-person"></i></a>
      @endauth

      <a href="{{ route('cart.index') }}" class="pl-icon-btn">
        <i class="bi bi-bag"></i>
        <span class="pl-cart-badge" data-cart-badge style="{{ session()->has('cart') && array_sum(array_column(session('cart'), 'quantity')) > 0 ? '' : 'display:none;' }}">
          {{ session()->has('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}
        </span>
      </a>
    </div>
  </div>

  <!-- Bottom Navigation Menu (Dynamic) -->
  <div class="pl-bottom-bar">
    <!-- Dynamic Categories -->
    @if(isset($headerCategories) && $headerCategories->count() > 0)
      @foreach($headerCategories->take(5) as $cat)
        <div class="dropdown">
          <a href="#" class="pl-nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
            {{ $cat->name }} <i class="bi bi-chevron-down ms-1"></i>
          </a>
          <ul class="dropdown-menu shadow-sm border-0" style="border-radius: 12px; margin-top: 10px;">
            <li><a class="dropdown-item fw-bold text-success" href="{{ url('/shop?cat=' . $cat->slug) }}">All {{ $cat->name }}</a></li>
            <li><hr class="dropdown-divider"></li>
            @foreach($cat->products->take(4) as $prod)
              <li><a class="dropdown-item py-2 text-muted" href="{{ route('product.show', $prod->slug) }}">{{ $prod->name }}</a></li>
            @endforeach
          </ul>
        </div>
      @endforeach
    @else
      <a href="{{ url('/shop') }}" class="pl-nav-link">Plants <i class="bi bi-chevron-down"></i></a>
      <a href="{{ url('/shop') }}" class="pl-nav-link">Seeds <i class="bi bi-chevron-down"></i></a>
      <a href="{{ url('/shop') }}" class="pl-nav-link">Pots & Planters <i class="bi bi-chevron-down"></i></a>
      <a href="{{ url('/shop') }}" class="pl-nav-link">Plant Care <i class="bi bi-chevron-down"></i></a>
      <a href="{{ url('/shop') }}" class="pl-nav-link">Accessories <i class="bi bi-chevron-down"></i></a>
    @endif
    
    <!-- Static Links -->
    <a href="{{ url('/shop?highlight=sale') }}" class="pl-nav-link">Combos</a>
    <a href="{{ url('/contact') }}" class="pl-nav-link">Bulk Order</a>
    <a href="{{ url('/dashboard') }}" class="pl-nav-link">Track Order</a>
  </div>
</header>


<!-- ===================== MOBILE HEADER (d-lg-none) ===================== -->
<header class="d-lg-none" style="background: #ffffff; position: sticky; top: 0; z-index: 1020; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-bottom: 1px solid #f0f0f0;">
  <div class="pl-middle-bar" style="padding: 10px 15px;">
    <!-- Hamburger -->
    <div style="flex: 1; display: flex; justify-content: flex-start;">
      <button class="btn p-0 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuOffcanvas">
        <i class="bi bi-list fs-1 text-dark"></i>
      </button>
    </div>

    <!-- Logo -->
    <a href="{{ route('home') }}" class="pl-logo-area" style="flex: 1; justify-content: center; text-align: center;">
      <div class="pl-logo-icon" style="font-size: 1.5rem;"><i class="bi bi-flower1"></i></div>
      <div class="pl-logo-text" style="font-size: 1.2rem; white-space: nowrap;">Urban Nursery</div>
      <div class="pl-logo-curve" style="width: 40px; height: 3px;"></div>
    </a>

    <!-- Icons -->
    <div class="pl-icons-area" style="flex: 1; display: flex; justify-content: flex-end; gap: 15px;">
      <a href="javascript:void(0)" class="pl-icon-btn" onclick="document.getElementById('searchOverlay').classList.add('active')">
        <i class="bi bi-search"></i>
      </a>
      <a href="{{ route('cart.index') }}" class="pl-icon-btn">
        <i class="bi bi-bag"></i>
        <span class="pl-cart-badge" data-cart-badge style="top:-4px; right:-6px; {{ session()->has('cart') && array_sum(array_column(session('cart'), 'quantity')) > 0 ? '' : 'display:none;' }}">
          {{ session()->has('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}
        </span>
      </a>
    </div>
  </div>
</header>

<!-- Mobile Offcanvas Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenuOffcanvas" style="width: 280px; z-index: 1055;">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title d-flex align-items-center gap-2" style="font-weight: 700; color: #111;">
      <i class="bi bi-flower1" style="color: #7DA948;"></i> Urban Nursery
    </h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="list-group list-group-flush mb-4">
      <!-- Dynamic Mobile Categories -->
      <div class="accordion accordion-flush" id="mobileMenuAccordion">
      @if(isset($headerCategories) && $headerCategories->count() > 0)
        @foreach($headerCategories as $index => $cat)
          <div class="accordion-item border-0">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed fw-bold px-3 py-2 text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#mobCat{{ $index }}" style="box-shadow: none; background: transparent;">
                {{ $cat->name }}
              </button>
            </h2>
            <div id="mobCat{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#mobileMenuAccordion">
              <div class="accordion-body p-0 ps-4">
                <ul class="list-unstyled mb-0 pb-2">
                  <li><a href="{{ url('/shop?cat=' . $cat->slug) }}" class="text-success text-decoration-none d-block py-2 fw-semibold">View All</a></li>
                  @foreach($cat->products->take(4) as $prod)
                    <li><a href="{{ route('product.show', $prod->slug) }}" class="text-muted text-decoration-none d-block py-1">{{ $prod->name }}</a></li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
        @endforeach
      @else
        <li class="list-group-item border-0"><a href="{{ url('/shop') }}" class="text-dark text-decoration-none fw-bold d-block py-1">Plants</a></li>
        <li class="list-group-item border-0"><a href="{{ url('/shop') }}" class="text-dark text-decoration-none fw-bold d-block py-1">Seeds</a></li>
        <li class="list-group-item border-0"><a href="{{ url('/shop') }}" class="text-dark text-decoration-none fw-bold d-block py-1">Pots & Planters</a></li>
      @endif
      </div>
      <li class="list-group-item border-0"><hr class="my-1"></li>
      <li class="list-group-item border-0"><a href="{{ url('/shop?highlight=sale') }}" class="text-dark text-decoration-none fw-bold d-block py-1">Combos</a></li>
      <li class="list-group-item border-0"><a href="{{ url('/contact') }}" class="text-dark text-decoration-none fw-bold d-block py-1">Bulk Order</a></li>
      <li class="list-group-item border-0"><a href="{{ url('/dashboard') }}" class="text-dark text-decoration-none fw-bold d-block py-1">Track Order</a></li>
    </ul>

    <div class="px-3">
      @auth
        <a href="{{ route('dashboard') }}" class="btn text-white w-100 mb-2" style="background:#274833; border-radius:30px; font-weight:600;">My Account</a>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button type="submit" class="btn btn-outline-danger w-100" style="border-radius:30px; font-weight:600;">Log Out</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="btn text-white w-100 mb-2" style="background:#7DA948; border-radius:30px; font-weight:600;">Log In / Register</a>
      @endauth
    </div>
  </div>
</div>

<!-- Full Screen Search Overlay -->
<div class="search-overlay" id="searchOverlay">
  <i class="bi bi-x-lg close-search" onclick="document.getElementById('searchOverlay').classList.remove('active')"></i>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <form action="{{ route('shop') }}" method="GET" class="position-relative">
          <input type="text" name="search" class="form-control" placeholder="Search for plants, pots, seeds..." style="border: none; border-bottom: 2px solid #7DA948; border-radius: 0; background: transparent; font-size: 1.5rem; padding: 15px 50px 15px 0; box-shadow: none;" autofocus>
          <button type="submit" class="btn position-absolute top-50 translate-middle-y p-0 border-0" style="right: 10px; font-size: 1.5rem; color: #7DA948;">
            <i class="bi bi-search"></i>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
