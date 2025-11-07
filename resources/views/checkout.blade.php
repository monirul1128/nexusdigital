<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout — {{ $product->title }}</title>
    <style>
        body{font-family:Inter,system-ui,Arial;margin:0;background:#f7f7f6;color:#111;padding:24px}
        .wrap{max-width:720px;margin:0 auto;background:#fff;padding:24px;border-radius:12px}
        .row{display:flex;gap:16px;align-items:center}
        img{width:140px;height:120px;object-fit:cover;border-radius:8px}
        .price{font-weight:700;color:#ff6b00}
        .buybtn{background:#ff6b00;color:#fff;padding:10px 14px;border:0;border-radius:8px;cursor:pointer}
    </style>
</head>
<body>
    <div class="wrap">
        <h2>Checkout</h2>
        <div class="row">
            <img src="{{ $product->image }}" alt="{{ $product->title }}">
            <div>
                <div style="font-weight:700">{{ $product->title }}</div>
                <div style="margin-top:6px;color:#6d6d6b">{{ $product->description }}</div>
                <div style="margin-top:10px" class="price">${{ number_format($product->price/100,2) }}</div>
            </div>
        </div>

        <form id="checkout-form" method="POST" action="/create-checkout-session" style="margin-top:20px">
            @csrf
            <input type="hidden" name="external_id" value="{{ $product->external_id }}">
            <button type="submit" class="buybtn">Pay with Stripe</button>
        </form>

        <hr style="margin:18px 0">

        <h3>Or pay Cash on Delivery</h3>
        <form id="cod-form" method="POST" action="/order-cod" style="margin-top:12px">
            @csrf
            <input type="hidden" name="external_id" value="{{ $product->external_id }}">
            <div style="display:grid;gap:10px">
                <input name="name" placeholder="Full name" required style="padding:12px;border-radius:6px;border:1px solid #e6e6e6">
                <input name="phone" placeholder="Phone number" required style="padding:12px;border-radius:6px;border:1px solid #e6e6e6">
                <textarea name="address" placeholder="Full address (street, city, postal code)" required rows="4" style="padding:12px;border-radius:6px;border:1px solid #e6e6e6"></textarea>
            </div>
            <button type="submit" class="buybtn" style="margin-top:12px">Order now (COD)</button>
        </form>

        <p style="margin-top:12px;color:#6d6d6b">(If Stripe isn't configured this will redirect you to a demo checkout page.)</p>
    </div>
</body>
</html>
