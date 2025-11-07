<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Order received</title>
    <style>body{font-family:Inter,system-ui,Arial;padding:24px;background:#f7f7f6} .card{max-width:720px;margin:0 auto;background:#fff;padding:20px;border-radius:10px}</style>
</head>
<body>
    <div class="card">
        <h2>Order received</h2>
        <p>Thank you. Your order <strong>{{ $order['id'] }}</strong> was received and will be prepared for Cash On Delivery.</p>
        <dl>
            <dt>Product</dt>
            <dd>{{ $order['product_title'] ?? $order['product_external_id'] }}</dd>
            <dt>Recipient</dt>
            <dd>{{ $order['name'] }} — {{ $order['phone'] }}</dd>
            <dt>Address</dt>
            <dd>{{ $order['address'] }}</dd>
        </dl>
        <p style="margin-top:10px">You can find saved orders in <code>storage/app/orders.json</code>.</p>
        <p><a href="/">Back to shop</a></p>
    </div>
</body>
</html>
