<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                // drop old unique index if exists
                try {
                    $table->dropUnique(['user_id', 'product_id']);
                } catch (\Exception $e) {
                    // ignore if index name differs or not present
                    try {
                        $table->dropUnique('cart_items_user_id_product_id_unique');
                    } catch (\Exception $_) {}
                }

                // add new unique composite including variant_id
                if (! Schema::hasColumn('cart_items', 'variant_id')) {
                    // variant_id column should already be added by prior migration; if not, add nullable
                    $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
                }

                // create composite unique index
                $table->unique(['user_id', 'product_id', 'variant_id'], 'cart_items_user_product_variant_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                try {
                    $table->dropUnique('cart_items_user_product_variant_unique');
                } catch (\Exception $e) {}

                // restore old unique on user_id + product_id
                $table->unique(['user_id', 'product_id']);
            });
        }
    }
};
