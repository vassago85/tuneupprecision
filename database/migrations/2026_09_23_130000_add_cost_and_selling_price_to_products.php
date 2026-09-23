<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('sku')->nullable()->unique()->after('slug');
            $table->unsignedInteger('cost_cents')->default(0)->after('description');
            $table->unsignedInteger('selling_ex_vat_cents')->nullable()->after('cost_cents');
            $table->boolean('round_price_up')->default(false)->after('selling_ex_vat_cents');
        });

        $now = now();

        foreach (require database_path('data/consignment_ioc49297.php') as $item) {
            DB::table('products')->updateOrInsert(
                ['sku' => $item['sku']],
                [
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'category' => $item['category'],
                    'description' => null,
                    'cost_cents' => $item['cost_rands'] * 100,
                    'stock_qty' => $item['qty'],
                    'selling_ex_vat_cents' => null,
                    'round_price_up' => false,
                    'price_cents' => 0,
                    'is_active' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        $skus = array_column(require database_path('data/consignment_ioc49297.php'), 'sku');

        DB::table('products')->whereIn('sku', $skus)->delete();

        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['sku']);
            $table->dropColumn(['sku', 'cost_cents', 'selling_ex_vat_cents', 'round_price_up']);
        });
    }
};
