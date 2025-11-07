<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Product::create([
            'external_id' => 'prod_1',
            'title' => 'UI Kit - Aurora',
            'description' => 'Modern UI kit',
            'price' => 2900,
            'image' => 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=800&q=80'
        ]);
        Product::create([
            'external_id' => 'prod_2',
            'title' => 'Template - LandingX',
            'description' => 'Responsive landing page',
            'price' => 4900,
            'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80'
        ]);
        Product::create([
            'external_id' => 'prod_3',
            'title' => 'Icon Set - Glyphs',
            'description' => '500+ vector icons',
            'price' => 1900,
            'image' => 'https://images.unsplash.com/photo-1518779578993-ec3579fee39f?auto=format&fit=crop&w=800&q=80'
        ]);
    }
}
