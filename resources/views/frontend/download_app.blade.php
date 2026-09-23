<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Download Urban Nursery App</title>
<meta name="description" content="Download the Urban Nursery Web App for the best shopping experience on your phone.">
<meta name="robots" content="index, follow">

<!-- PWA Meta Tags -->
@include('frontend.partials.pwa_head')

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/style.css?v=' . filemtime(public_path('css/style.css'))) }}">

<style>
    body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
    .app-card { background: #ffffff; border-radius: 20px; box-shadow: 0 10px 40px rgba(255,107,0,0.1); padding: 40px 20px; text-align: center; max-width: 450px; margin: 0 auto; }
    .app-icon-large { width: 120px; height: 120px; border-radius: 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); margin-bottom: 24px; }
    .feature-list { text-align: left; margin: 30px 0; padding: 0 20px; }
    .feature-item { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; font-size: 1rem; color: #475569; font-weight: 500; }
    .feature-item i { font-size: 1.5rem; color: var(--pl-primary); }
</style>
</head>
<body>

@include('frontend.partials.header')

<main class="container py-5 mt-4 mb-5">
    <div class="app-card">
        <img src="{{ asset('images/icons/icon-512x512.png') }}" alt="Urban Nursery App" class="app-icon-large">
        <h1 class="fw-bold" style="font-family: 'Outfit', sans-serif; color: var(--pl-primary-dark); font-size: 2rem;">Urban Nursery App</h1>
        <p class="text-muted">Fast, easy, and offline-ready</p>
        
        <div class="feature-list">
            <div class="feature-item">
                <i class="bi bi-phone"></i>
                <span>Direct Home-Screen Access</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-lightning-charge"></i>
                <span>Faster Loading & Performance</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-bag-check"></i>
                <span>One-tap plant Shopping</span>
            </div>
        </div>

        <a href="{{ asset('app.apk') }}" download class="btn btn-pl-primary w-100 py-3 rounded-pill shadow fw-bold fs-5 d-flex justify-content-center align-items-center gap-2 text-decoration-none">
            <i class="bi bi-download"></i> Download Android App (.apk)
        </a>
        
        <p class="text-muted small mt-4">
            For iPhone (iOS): Open this page in Safari, tap the Share <i class="bi bi-box-arrow-up"></i> icon, and select <b>Add to Home Screen</b>.
        </p>
    </div>
</main>

@include('frontend.partials.footer')
@include('frontend.partials.bottom_nav')

<!-- PWA Installation Banners and Scripts -->
@include('frontend.partials.pwa_script')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  window.pl_csrf = '{{ csrf_token() }}';
</script>
<script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
