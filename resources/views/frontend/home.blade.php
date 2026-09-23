<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Urban Nursery - Bring Nature Home</title>
<meta name="description" content="Buy premium indoor and outdoor plants online.">
<link rel="icon" href="{{ asset('images/favicon.ico') }}">

<!-- Preconnect for Performance -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- CSS & Fonts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<style>
  :root {
    --primary-green: #1B4332;
    --secondary-green: #2D6A4F;
    --light-green: #D8F3DC;
    --accent-orange: #E07A5F;
    --bg-color: #F8F9FA;
    --text-main: #333333;
  }
  
  body { background-color: var(--bg-color); font-family: 'Outfit', sans-serif; color: var(--text-main); }
  
  .pl-main-container { max-width: 1300px; margin: 0 auto; }
  
  .section-title { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--primary-green); text-align: center; margin-bottom: 2rem; position: relative; }
  
  /* Category Circles */
  .scrollbar-hidden::-webkit-scrollbar { display: none; }
  .cat-circle-wrap { text-align: center; text-decoration: none; display: flex; flex-direction: column; align-items: center; width: 110px; flex-shrink: 0; transition: all 0.3s ease; }
  .cat-circle { width: 90px; height: 90px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 8px; border: 2px solid transparent; transition: all 0.3s ease; padding: 5px; }
  .cat-circle img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
  .cat-circle-wrap:hover .cat-circle { border-color: var(--secondary-green); transform: translateY(-5px); box-shadow: 0 8px 20px rgba(45, 106, 79, 0.15); }
  .cat-title { font-size: 0.85rem; font-weight: 600; color: #555; }
  .cat-circle-wrap:hover .cat-title { color: var(--secondary-green); }

  /* Hero Banner */
  .hero-banner-wrap { border-radius: 20px; overflow: hidden; position: relative; margin-bottom: 3rem; }
  .hero-banner-img { width: 100%; aspect-ratio: 21/9; object-fit: cover; }
  .hero-content { position: absolute; top: 50%; left: 10%; transform: translateY(-50%); background: rgba(255, 255, 255, 0.9); padding: 2.5rem; border-radius: 15px; max-width: 450px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); backdrop-filter: blur(5px); }
  .hero-content h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 700; color: var(--primary-green); line-height: 1.2; margin-bottom: 1rem; }
  .btn-shop-now { background: var(--primary-green); color: #fff; border: none; padding: 12px 30px; border-radius: 30px; font-weight: 600; font-size: 1.1rem; transition: background 0.3s; }
  .btn-shop-now:hover { background: var(--secondary-green); color: #fff; }

  /* Product Card */
  .pl-product-card { background: #ffffff; border-radius: 12px; padding: 12px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.04); border: 1px solid #f2f2f2; transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; position: relative; }
  .pl-product-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.08); transform: translateY(-5px); border-color: var(--light-green); }
  .pl-card-img { width: 100%; aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; position: relative; border-radius: 8px; overflow: hidden; background: #f9f9f9; }
  .pl-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
  .pl-product-card:hover .pl-card-img img { transform: scale(1.08); }
  .pl-card-title { font-size: 0.95rem; font-weight: 600; color: var(--text-main); text-decoration: none; margin-bottom: 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .pl-card-title:hover { color: var(--secondary-green); }
  .pl-card-price { font-size: 1.1rem; font-weight: 700; color: var(--primary-green); margin-bottom: 12px; }
  .pl-card-price strike { font-size: 0.85rem; color: #999; font-weight: 500; margin-left: 6px; }
  .btn-add { background: #ffffff; color: var(--secondary-green); border: 1px solid var(--secondary-green); font-weight: 600; font-size: 0.85rem; padding: 8px 15px; border-radius: 30px; width: 100%; transition: all 0.3s ease; }
  .btn-add:hover { background: var(--secondary-green); color: #fff; }
  .pl-wishlist-btn { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,0.9); border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #aaa; z-index: 2; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
  .pl-wishlist-btn:hover { color: #e63946; transform: scale(1.1); }
  .pl-tag-sale { position: absolute; top: 8px; left: 8px; background: var(--accent-orange); color: #fff; font-size: 0.7rem; font-weight: 700; padding: 3px 8px; border-radius: 4px; z-index: 2; }

  /* Shop By Budget */
  .budget-card { border-radius: 16px; overflow: hidden; position: relative; aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
  .budget-card:hover { transform: scale(1.03); }
  .budget-card img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 1; filter: brightness(0.6); transition: filter 0.3s; }
  .budget-card:hover img { filter: brightness(0.4); }
  .budget-content { position: relative; z-index: 2; text-align: center; }
  .budget-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; margin-bottom: 5px; }
  .budget-btn { background: white; color: var(--primary-green); font-weight: 600; padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; display: inline-block; margin-top: 5px; }

  /* Ideal Plants Banners */
  .ideal-banner { position: relative; border-radius: 16px; overflow: hidden; aspect-ratio: 3/4; display: block; }
  .ideal-banner img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
  .ideal-banner:hover img { transform: scale(1.05); }
  .ideal-banner-content { position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; }
  .ideal-banner-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 5px; }

  /* Feature Strip */
  .feature-strip { background: var(--light-green); border-radius: 12px; padding: 20px; display: flex; align-items: center; justify-content: space-around; flex-wrap: wrap; gap: 15px; margin: 3rem 0; }
  .feature-item { display: flex; align-items: center; gap: 10px; font-weight: 600; color: var(--primary-green); font-size: 1.1rem; }
  .feature-item i { font-size: 1.5rem; color: var(--secondary-green); }

  /* Blogs */
  .blog-card { border-radius: 16px; overflow: hidden; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
  .blog-card img { width: 100%; aspect-ratio: 16/10; object-fit: cover; }
  .blog-content { padding: 20px; }
  .blog-title { font-size: 1.1rem; font-weight: 700; color: var(--primary-green); margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .blog-desc { font-size: 0.9rem; color: #666; margin-bottom: 15px; }
  .blog-link { color: var(--secondary-green); font-weight: 600; text-decoration: none; }

  @media(max-width: 768px) {
    .hero-banner-img { aspect-ratio: 1/1; }
    .hero-content { left: 5%; right: 5%; padding: 1.5rem; text-align: center; }
    .hero-content h1 { font-size: 2rem; }
    .section-title { font-size: 1.5rem; }
    .cat-circle { width: 70px; height: 70px; }
    .budget-card { aspect-ratio: 2/1; }
  }
</style>
</head>
<body>

@include('frontend.partials.header')

<main class="container-fluid px-3 px-xl-5 pl-main-container py-4">

  <!-- ===================== TOP CATEGORIES ===================== -->
  <section class="mb-4">
    <div class="d-flex flex-nowrap overflow-x-auto pb-3 gap-3 justify-content-start justify-content-md-center scrollbar-hidden" style="-webkit-overflow-scrolling: touch;">
      @foreach($categories as $cat)
        <a href="{{ url('/shop?cat=' . $cat->slug) }}" class="cat-circle-wrap">
          <div class="cat-circle">
             <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" loading="lazy">
          </div>
          <div class="cat-title">{{ $cat->name }}</div>
        </a>
      @endforeach
      <!-- Add some placeholders if categories are empty to match design -->
      @if($categories->count() < 4)
        @php $placeholders = ['Air Purifying', 'Indoor Plants', 'Outdoor Plants', 'Flowering', 'Pots & Planters']; @endphp
        @foreach($placeholders as $ph)
        <a href="{{ url('/shop') }}" class="cat-circle-wrap">
          <div class="cat-circle">
             <img src="https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?q=80&w=200&auto=format&fit=crop" alt="{{ $ph }}" loading="lazy">
          </div>
          <div class="cat-title">{{ $ph }}</div>
        </a>
        @endforeach
      @endif
    </div>
  </section>

  <!-- ===================== HERO BANNER ===================== -->
  @if(isset($banners) && $banners->count() > 0)
    <div id="heroBannerCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
      <div class="carousel-inner" style="border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        @foreach($banners as $index => $banner)
          <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
            <div class="hero-banner-wrap" style="margin-bottom: 0; border-radius: 0;">
              <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" class="hero-banner-img w-100">
              <div class="hero-content">
                <h1>{{ $banner->title }}</h1>
                <p class="text-muted mb-4">{{ $banner->subtitle }}</p>
                <a href="{{ url($banner->link) }}" class="btn-shop-now d-inline-block text-decoration-none">Explore</a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
      @if($banners->count() > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="prev" style="width: 5%;">
          <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true" style="padding: 1.5rem; opacity: 0.6;"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="next" style="width: 5%;">
          <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true" style="padding: 1.5rem; opacity: 0.6;"></span>
          <span class="visually-hidden">Next</span>
        </button>
      @endif
    </div>
  @endif

  <!-- ===================== BEST SELLING PRODUCTS ===================== -->
  <section class="mb-5">
    <h2 class="section-title">Our Best Selling</h2>
    <div class="row g-3 g-md-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
      @foreach($bestSellers->take(5) as $product)
      <div class="col">
        <div class="pl-product-card">
          <div class="pl-card-img">
            @if($product->sale_price)
              <span class="pl-tag-sale">Sale</span>
            @endif
            <button class="pl-wishlist-btn" onclick="PL.toggleWishlist('{{ $product->id }}')"><i class="bi bi-heart"></i></button>
            <a href="{{ route('product.show', $product->slug) }}">
              <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy">
            </a>
          </div>
          <a href="{{ route('product.show', $product->slug) }}" class="pl-card-title">{{ $product->name }}</a>
          <div class="pl-card-price">&#8377;{{ number_format($product->sale_price ?? $product->price, 2) }}
            @if($product->sale_price)
              <strike>&#8377;{{ number_format($product->price, 2) }}</strike>
            @endif
          </div>
          <div class="mt-auto">
            <button class="btn-add" onclick="PL.addToCartById('{{ $product->id }}')">Add to Cart</button>
          </div>
        </div>
      </div>
      @endforeach
      
      <!-- Placeholder cards if less than 5 products exist -->
      @if($bestSellers->count() == 0)
        @for($i=0; $i<5; $i++)
        <div class="col">
          <div class="pl-product-card">
            <div class="pl-card-img">
              <a href="#"><img src="https://images.unsplash.com/photo-1485955900006-10f4d324d411?q=80&w=400&auto=format&fit=crop" alt="Plant"></a>
            </div>
            <a href="#" class="pl-card-title">Beautiful Jade Plant</a>
            <div class="pl-card-price">&#8377;299.00</div>
            <div class="mt-auto">
              <button class="btn-add">Add to Cart</button>
            </div>
          </div>
        </div>
        @endfor
      @endif
    </div>
    <div class="text-center mt-4">
      <a href="{{ url('/shop') }}" class="btn btn-outline-success" style="border-radius: 20px; padding: 8px 25px; font-weight: 600; border-color: var(--secondary-green); color: var(--secondary-green);">View All</a>
    </div>
  </section>

  <!-- ===================== SHOP BY BUDGET ===================== -->
  <section class="mb-5">
    <h2 class="section-title">Shop By Budget</h2>
    <div class="row g-3 g-md-4">
      <div class="col-6 col-md-3">
        <a href="{{ url('/shop?max_price=300') }}" class="budget-card">
          <img src="https://images.unsplash.com/photo-1497250681960-ef046c08a56e?q=80&w=600&auto=format&fit=crop" alt="Under 300">
          <div class="budget-content">
            <div class="budget-title">Under ₹300</div>
            <div class="budget-btn">Plants</div>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="{{ url('/shop?max_price=500') }}" class="budget-card">
          <img src="https://images.unsplash.com/photo-1463320726281-696a485928c7?q=80&w=600&auto=format&fit=crop" alt="Under 500">
          <div class="budget-content">
            <div class="budget-title">Under ₹500</div>
            <div class="budget-btn">Plants</div>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="{{ url('/shop?max_price=800') }}" class="budget-card">
          <img src="https://images.unsplash.com/photo-1512428559087-560fa5ceab42?q=80&w=600&auto=format&fit=crop" alt="Under 800">
          <div class="budget-content">
            <div class="budget-title">Under ₹800</div>
            <div class="budget-btn">Plants</div>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="{{ url('/shop?max_price=1000') }}" class="budget-card">
          <img src="https://images.unsplash.com/photo-1584589167171-541ce45f1eea?q=80&w=600&auto=format&fit=crop" alt="Under 1000">
          <div class="budget-content">
            <div class="budget-title">Under ₹1000</div>
            <div class="budget-btn">Plants</div>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- ===================== SUCCULENT COMBOS ===================== -->
  @if($categorySections->count() > 0)
    @php $succulentCat = $categorySections->first(); @endphp
    <section class="mb-5">
      <h2 class="section-title">{{ $succulentCat->name }}</h2>
      <div class="row g-3 g-md-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
        @foreach($succulentCat->products as $product)
        <div class="col">
          <div class="pl-product-card" style="border-radius: 50% 50% 16px 16px; padding-top: 5px;">
            <div class="pl-card-img" style="border-radius: 50%; aspect-ratio: 1/1; margin-bottom: 10px;">
              <a href="{{ route('product.show', $product->slug) }}">
                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" style="border-radius: 50%;">
              </a>
            </div>
            <a href="{{ route('product.show', $product->slug) }}" class="pl-card-title">{{ $product->name }}</a>
            <div class="pl-card-price">&#8377;{{ number_format($product->sale_price ?? $product->price, 2) }}</div>
            <div class="mt-auto">
              <button class="btn-add" onclick="PL.addToCartById('{{ $product->id }}')">Add to Cart</button>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </section>
  @endif

  <!-- ===================== IDEAL HOME & OFFICE PLANTS ===================== -->
  <section class="mb-5">
    <h2 class="section-title">Ideal Home & Office Plants</h2>
    <div class="row g-3 g-md-4">
      <div class="col-12 col-md-4">
        <a href="{{ url('/shop') }}" class="ideal-banner">
          <img src="https://images.unsplash.com/photo-1614594975525-e45190c55d0b?q=80&w=800&auto=format&fit=crop" alt="Snake Plant">
          <div class="ideal-banner-content">
            <div class="ideal-banner-title">Low Maintenance</div>
            <div class="small">Perfect for beginners</div>
          </div>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a href="{{ url('/shop') }}" class="ideal-banner">
          <img src="https://images.unsplash.com/photo-1485955900006-10f4d324d411?q=80&w=800&auto=format&fit=crop" alt="Monstera">
          <div class="ideal-banner-content">
            <div class="ideal-banner-title">Air Purifying</div>
            <div class="small">Breathe easy at home</div>
          </div>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a href="{{ url('/shop') }}" class="ideal-banner">
          <img src="https://images.unsplash.com/photo-1497250681960-ef046c08a56e?q=80&w=800&auto=format&fit=crop" alt="Money Plant">
          <div class="ideal-banner-content">
            <div class="ideal-banner-title">Pet Friendly</div>
            <div class="small">Safe for your furry friends</div>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- ===================== SHOP BY VIBE ===================== -->
  <section class="mb-5 pt-3">
    <h2 class="section-title">Shop by Vibe</h2>
    <div class="d-flex flex-nowrap overflow-x-auto pb-3 gap-3 justify-content-start justify-content-md-center scrollbar-hidden" style="-webkit-overflow-scrolling: touch;">
      @php
        $vibes = [
          ['name' => 'Air Purifying', 'img' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?q=80&w=200'],
          ['name' => 'Pet Friendly', 'img' => 'https://images.unsplash.com/photo-1497250681960-ef046c08a56e?q=80&w=200'],
          ['name' => 'Low Light', 'img' => 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?q=80&w=200'],
          ['name' => 'Easy Care', 'img' => 'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?q=80&w=200'],
          ['name' => 'Office Desk', 'img' => 'https://images.unsplash.com/photo-1463320726281-696a485928c7?q=80&w=200'],
          ['name' => 'Gifting', 'img' => 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?q=80&w=200']
        ];
      @endphp
      @foreach($vibes as $vibe)
        <a href="{{ url('/shop') }}" class="cat-circle-wrap" style="width: 90px;">
          <div class="cat-circle" style="width: 70px; height: 70px;">
             <img src="{{ $vibe['img'] }}" alt="{{ $vibe['name'] }}" loading="lazy">
          </div>
          <div class="cat-title" style="font-size: 0.75rem;">{{ $vibe['name'] }}</div>
        </a>
      @endforeach
    </div>
  </section>

  <!-- ===================== FEATURES STRIP ===================== -->
  <div class="feature-strip">
    <div class="feature-item">
      <i class="bi bi-star-fill"></i>
      <span>Premium Plants Starting at ₹299</span>
    </div>
    <div class="feature-item">
      <i class="bi bi-truck"></i>
      <span>Free delivery above ₹399</span>
    </div>
  </div>

  <!-- ===================== INDIA'S LARGEST ADENIUM COLLECTION ===================== -->
  @if($categorySections->count() > 1)
    @php $adeniumCat = $categorySections[1]; @endphp
    <section class="mb-5">
      <h2 class="section-title">India's largest {{ $adeniumCat->name }} collection</h2>
      <div class="row g-3 g-md-4 row-cols-2 row-cols-md-3 row-cols-lg-5">
        @foreach($adeniumCat->products as $product)
        <div class="col">
          <div class="pl-product-card">
            <div class="pl-card-img">
              <a href="{{ route('product.show', $product->slug) }}">
                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy">
              </a>
            </div>
            <a href="{{ route('product.show', $product->slug) }}" class="pl-card-title">{{ $product->name }}</a>
            <div class="pl-card-price">&#8377;{{ number_format($product->sale_price ?? $product->price, 2) }}</div>
            <div class="mt-auto">
              <button class="btn-add" onclick="PL.addToCartById('{{ $product->id }}')">Add to Cart</button>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </section>
  @endif

  <!-- ===================== TESTIMONIALS ===================== -->
  <section class="mb-5 py-4" style="background: var(--light-green); border-radius: 20px; padding: 2rem;">
    <h2 class="section-title" style="margin-bottom: 1.5rem;">Testimonials</h2>
    <div class="row justify-content-center">
      <div class="col-md-5 mb-3">
        <div class="bg-white p-4 rounded-4 shadow-sm h-100">
          <div class="d-flex align-items-center mb-3">
            <img src="https://randomuser.me/api/portraits/women/44.jpg" class="rounded-circle me-3" width="50" height="50">
            <div>
              <h5 class="mb-0 fw-bold" style="color: var(--primary-green);">Priya Sharma</h5>
              <div class="text-warning"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
            </div>
          </div>
          <p class="text-muted">"The plants arrived in perfect condition! The packaging was so secure, not a single leaf was damaged. Highly recommend Urban Nursery for indoor plants."</p>
        </div>
      </div>
      <div class="col-md-5 mb-3">
        <div class="bg-white p-4 rounded-4 shadow-sm h-100">
          <div class="d-flex align-items-center mb-3">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" class="rounded-circle me-3" width="50" height="50">
            <div>
              <h5 class="mb-0 fw-bold" style="color: var(--primary-green);">Rahul Verma</h5>
              <div class="text-warning"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
            </div>
          </div>
          <p class="text-muted">"Absolutely love the succulent combo! They look beautiful on my office desk. The soil quality provided is also top-notch. Will definitely order again."</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== BLOG POSTS ===================== -->
  <section class="mb-5">
    <h2 class="section-title">Blog Post</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="blog-card h-100">
          <img src="https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?q=80&w=800&auto=format&fit=crop" alt="Blog 1">
          <div class="blog-content">
            <h4 class="blog-title">10 Best Indoor Plants for Beginners</h4>
            <p class="blog-desc">Starting your plant journey? Discover the top 10 low-maintenance plants that are almost impossible to kill.</p>
            <a href="#" class="blog-link">Read More &rarr;</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="blog-card h-100">
          <img src="https://images.unsplash.com/photo-1512428559087-560fa5ceab42?q=80&w=800&auto=format&fit=crop" alt="Blog 2">
          <div class="blog-content">
            <h4 class="blog-title">How to Care for Your Succulents</h4>
            <p class="blog-desc">Watering, sunlight, and soil mix—everything you need to know to keep your succulents plump and colorful.</p>
            <a href="#" class="blog-link">Read More &rarr;</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="blog-card h-100">
          <img src="https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?q=80&w=800&auto=format&fit=crop" alt="Blog 3">
          <div class="blog-content">
            <h4 class="blog-title">The Magic of Adenium (Desert Rose)</h4>
            <p class="blog-desc">Learn how to make your Adenium bloom profusely and maintain a thick, healthy caudex.</p>
            <a href="#" class="blog-link">Read More &rarr;</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===================== ABOUT Urban Nursery ===================== -->
  <section class="mb-5 text-center" style="max-width: 800px; margin: 0 auto;">
    <h2 class="section-title">About Urban Nursery</h2>
    <p class="text-muted" style="line-height: 1.8; font-size: 1.05rem;">
      Welcome to Urban Nursery, your ultimate destination for premium indoor and outdoor plants. We believe that integrating nature into your living spaces brings peace, joy, and better health. Our expertly curated selection includes everything from exotic succulents to lush air-purifying foliage, ensuring there's a perfect green companion for everyone. We take pride in our secure packaging and prompt delivery across India, so your plants arrive happy and healthy.
    </p>
  </section>

  <!-- ===================== FAQ ===================== -->
  <section class="mb-5" style="max-width: 800px; margin: 0 auto;">
    <h2 class="section-title">FAQ</h2>
    <div class="accordion" id="faqAccordion">
      <div class="accordion-item" style="border-radius: 12px; margin-bottom: 10px; overflow: hidden; border: 1px solid #eee;">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" style="background: #fff; color: var(--primary-green); font-weight: 600;">
            How are the plants packaged for delivery?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-muted">
            We use specially designed ventilated boxes with secure plant locks to ensure the pot stays in place and the leaves are protected during transit.
          </div>
        </div>
      </div>
      <div class="accordion-item" style="border-radius: 12px; margin-bottom: 10px; overflow: hidden; border: 1px solid #eee;">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="background: #fff; color: var(--primary-green); font-weight: 600;">
            Do you deliver all over India?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-muted">
            Yes, we ship our plants to almost all pin codes across India using trusted courier partners.
          </div>
        </div>
      </div>
      <div class="accordion-item" style="border-radius: 12px; overflow: hidden; border: 1px solid #eee;">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" style="background: #fff; color: var(--primary-green); font-weight: 600;">
            What if my plant arrives damaged?
          </button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body text-muted">
            Don't worry! We have a hassle-free replacement policy. Just share a photo of the damaged plant within 24 hours of delivery, and we'll send a replacement.
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

@include('frontend.partials.footer')
@include('frontend.partials.bottom_nav')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  window.pl_csrf = '{{ csrf_token() }}';
</script>
<script src="{{ asset('js/script.js') }}"></script>

</body>
</html>

