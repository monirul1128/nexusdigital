<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;

// Home - product grid
Route::get('/', function () {
    $products = Product::all();
    return view('welcome', compact('products'));
});

// Product page by external_id
Route::get('/product/{id}', function ($id) {
    $product = Product::where('external_id', $id)->firstOrFail();
    return view('product', compact('product'));
});

// Checkout page by external_id
Route::get('/checkout/{external_id}', function ($external_id) {
    $product = Product::where('external_id', $external_id)->firstOrFail();
    return view('checkout', compact('product'));
});

// Stripe checkout endpoint
Route::post('/create-checkout-session', function (Request $request) {
    $items = $request->input('items', []);
    $stripeKey = env('STRIPE_SECRET_KEY');
    if (!$stripeKey) {
        return response()->json(['url' => url('/?success=true'), 'message' => 'Stripe not configured - dev redirect']);
    }

    $stripe = new \Stripe\StripeClient($stripeKey);
    $line_items = array_map(function($i){
        return [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => $i['title']],
                'unit_amount' => $i['price']
            ],
            'quantity' => $i['quantity']
        ];
    }, $items);

    $session = $stripe->checkout->sessions->create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => url('/?success=true'),
        'cancel_url' => url('/?canceled=true'),
    ]);

    return response()->json(['url' => $session->url]);
});

// Cash On Delivery order route
Route::post('/order-cod', function (Request $request) {
    $data = $request->validate([
        'external_id' => 'required|string',
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:50',
        'address' => 'required|string|max:1000',
    ]);

    $product = Product::where('external_id', $data['external_id'])->first();

    $order = [
        'id' => uniqid('ord_'),
        'product_external_id' => $data['external_id'],
        'product_title' => $product?->title ?? null,
        'price' => $product?->price ?? null,
        'name' => $data['name'],
        'phone' => $data['phone'],
        'address' => $data['address'],
        'payment_method' => 'cash_on_delivery',
        'created_at' => now()->toDateTimeString(),
    ];

    $path = storage_path('app/orders.json');
    $orders = [];
    if (file_exists($path)) {
        $contents = file_get_contents($path);
        $orders = json_decode($contents, true) ?? [];
    }

    $orders[] = $order;
    file_put_contents($path, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return redirect()->route('order.success', ['id' => $order['id']]);
});

// Order success page route
Route::get('/order-success/{id}', function ($id) {
    $path = storage_path('app/orders.json');
    $orders = [];
    if (file_exists($path)) {
        $orders = json_decode(file_get_contents($path), true) ?? [];
    }
    $order = collect($orders)->firstWhere('id', $id);
    if (! $order) {
        abort(404);
    }
    return view('order-success', ['order' => $order]);
})->name('order.success');

// Dashboard and profile routes
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
