<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $product->title }}</title>
    <style>body{font-family:Inter,system-ui,Arial;padding:24px;background:#f7f7f6} .wrap{max-width:900px;margin:0 auto;background:#fff;padding:22px;border-radius:10px} img{max-width:360px;width:100%;border-radius:8px}</style>
</head>
<body>
    <div class="wrap">
        <a href="/">← Back to shop</a>
        <div style="display:flex;gap:24px;margin-top:12px">
            <img src="{{ $product->image }}" alt="{{ $product->title }}">
            <div>
                <h1 style="margin:0">{{ $product->title }}</h1>
                <p style="color:#6d6d6d">{{ $product->description }}</p>
                <div style="font-weight:700;color:#ff6b00;margin-top:12px">${{ number_format($product->price/100,2) }}</div>
                <p style="margin-top:16px"><a href="/checkout/{{ $product->external_id }}" class="buybtn" style="display:inline-block;padding:10px 14px;background:#ff6b00;color:#fff;border-radius:8px;text-decoration:none">Buy Now</a></p>
            </div>
        </div>
    </div>
</body>
</html>
