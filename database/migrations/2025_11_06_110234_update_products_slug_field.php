<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем только новые колонки, пропуская те, что уже существуют
        if (! Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->unique()->nullable();
            });
        }

        if (! Schema::hasColumn('products', 'long_description')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('long_description')->nullable();
            });
        }

        if (! Schema::hasColumn('products', 'sale_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('sale_price', 10, 2)->nullable();
            });
        }

        if (! Schema::hasColumn('products', 'stock_quantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('stock_quantity')->default(0);
            });
        }

        if (! Schema::hasColumn('products', 'sku')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('sku')->unique()->nullable();
            });
        }

        if (! Schema::hasColumn('products', 'is_featured')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false);
            });
        }

        if (! Schema::hasColumn('products', 'is_active')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
        }

        if (! Schema::hasColumn('products', 'weight')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('weight', 8, 2)->nullable();
            });
        }

        if (! Schema::hasColumn('products', 'attributes')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('attributes')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Drop only columns that exist to avoid errors in test DBs
        $cols = [
            'slug', 'long_description', 'sale_price',
            'stock_quantity', 'sku', 'is_featured', 'is_active',
            'weight', 'attributes',
        ];

        foreach ($cols as $col) {
            if (Schema::hasColumn('products', $col)) {
                Schema::table('products', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
