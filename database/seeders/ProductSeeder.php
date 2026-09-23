<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The four shop merch lines. Safe to run on every deploy: an existing
 * product is left alone, so stock and prices Dirk has changed are kept.
 * Consignment stock is loaded by migration and stays unpublished.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Tune Up Trucker Cap',
                'category' => 'Headwear',
                'description' => '3D puff-embroidered trucker cap in tactical charcoal with a copper reticle.',
                'price_cents' => 32000,
                'stock_qty' => 25,
            ],
            [
                'name' => 'Reticle Morale Patch',
                'category' => 'Patch · Velcro',
                'description' => 'PVC velcro-backed morale patch featuring the Tune Up reticle mark.',
                'price_cents' => 15000,
                'stock_qty' => 40,
            ],
            [
                'name' => 'Weatherproof DOPE Cards',
                'category' => 'Range · Data',
                'description' => 'Set of 5 weatherproof DOPE cards for logging your ballistic solution on the line.',
                'price_cents' => 18000,
                'stock_qty' => 60,
            ],
            [
                'name' => 'Mini IPSC Gong 200mm',
                'category' => 'Steel · 6mm',
                'description' => '200 mm AR500 mini IPSC gong, rated for 6 mm centrefire at distance.',
                'price_cents' => 69000,
                'stock_qty' => 0,
            ],
        ];

        foreach ($products as $data) {
            Product::query()->firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'price_cents' => $data['price_cents'],
                    'stock_qty' => $data['stock_qty'],
                    'is_active' => true,
                ],
            );
        }
    }
}
