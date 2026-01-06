<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                if (! Schema::hasColumn('cart_items', 'variant_id')) {
                    $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('wishlist_items')) {
            Schema::table('wishlist_items', function (Blueprint $table) {
                if (! Schema::hasColumn('wishlist_items', 'variant_id')) {
                    $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (! Schema::hasColumn('order_items', 'variant_id')) {
                    $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'variant_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('variant_id');
            });
        }

        if (Schema::hasTable('wishlist_items') && Schema::hasColumn('wishlist_items', 'variant_id')) {
            Schema::table('wishlist_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('variant_id');
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('variant_id');
            });
        }
    }
};
