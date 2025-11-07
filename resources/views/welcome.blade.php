<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NexusDigital — Store</title>
    <style>
        /* Minimal storefront styles — tweak as needed */
        :root{--bg:#f7f7f6;--card:#fff;--muted:#6d6d6b;--accent:#ff6b00}
        html,body{height:100%;margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial}
        body{background:var(--bg);color:#111;display:flex;align-items:flex-start;justify-content:center;padding:32px}
        .container{width:100%;max-width:1100px}
        header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
        .brand{font-weight:700;color:var(--accent);font-size:20px}
        .hero{display:flex;align-items:center;gap:20px;padding:28px;border-radius:12px;background:linear-gradient(180deg,#fff,#fff);box-shadow:0 6px 20px rgba(12,12,12,0.06);margin-bottom:20px}
        .hero h1{margin:0;font-size:20px}
        .hero p{margin:4px 0 0;color:var(--muted)}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
        .card{background:var(--card);border-radius:10px;padding:12px;box-shadow:0 2px 8px rgba(12,12,12,0.04);display:flex;flex-direction:column}
        .card img{width:100%;height:160px;object-fit:cover;border-radius:6px}
        .product-link{color:inherit;text-decoration:none}
        .product-link:hover .title{text-decoration:underline}
        .meta{display:flex;justify-content:space-between;align-items:center;margin-top:10px}
        .title{font-size:14px;font-weight:600}
        .price{font-weight:700;color:var(--accent)}
        .desc{font-size:13px;color:var(--muted);margin-top:8px}
        .buy{margin-top:12px;padding:8px 10px;border-radius:8px;border:0;background:var(--accent);color:#fff;cursor:pointer}
        .buy-link{display:inline-block;margin-top:12px;padding:8px 10px;border-radius:8px;background:var(--accent);color:#fff;text-decoration:none}
        .empty{padding:60px;text-align:center;color:var(--muted)}
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="brand">NexusDigital</div>
            <nav>
                @if (Route::has('login') && auth()->check())
                    <a href="{{ url('/dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    @if (Route::has('register'))
                        <span style="margin-left:8px;"><a href="{{ route('register') }}">Register</a></span>
                    @endif
                @endif
            </nav>
        </header>

        <section class="hero">
            <div style="flex:1">
                <h1>Small shop — beautiful design</h1>
                <p>Browse our sample products. Click Buy Now to open the checkout flow (demo redirect if Stripe not configured).</p>
            </div>
            <div style="width:160px;text-align:right;color:var(--muted);font-size:13px">Open-source demo</div>
        </section>

        <main>
            @if ($products->isEmpty())
                <div class="empty">No products found. Run the seeder or check the database.</div>
            @else
                <div class="grid">
                    @foreach ($products as $product)
                        <div class="card" role="article">
                            <a class="product-link" href="/product/{{ $product->external_id }}">
                                <img src="{{ $product->image }}" alt="{{ $product->title }}">
                            </a>
                            <div class="meta">
                                <a class="product-link" href="/product/{{ $product->external_id }}"><div class="title">{{ $product->title }}</div></a>
                                <div class="price">${{ number_format($product->price / 100, 2) }}</div>
                            </div>
                            <div class="desc">{{ \Illuminate\Support\Str::limit($product->description, 90) }}</div>
                            <a class="buy-link" href="/checkout/{{ $product->external_id }}">Buy Now</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </main>

    </div>

    <script>
        // POST to /create-checkout-session when Buy Now clicked.
        async function createCheckout(externalId){
            try{
                const resp = await fetch("/create-checkout-session", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ external_id: externalId })
                });

                const json = await resp.json();
                if (json.url) {
                    window.location.href = json.url;
                } else if (json.redirect) {
                    window.location.href = json.redirect;
                } else {
                    alert('Checkout returned unexpected response. Open console for details.');
                    console.log('createCheckout response', json);
                }
            } catch (err) {
                console.error(err);
                alert('Could not start checkout — see console.');
            }
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.buy');
            if (!btn) return;
            const id = btn.getAttribute('data-product-id');
            if (!id) return;
            btn.disabled = true;
            btn.textContent = 'Opening…';
            createCheckout(id).finally(()=>{ btn.disabled = false; btn.textContent = 'Buy Now' });
        });
    </script>
</body>
</html>
