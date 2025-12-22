<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // safety: only proceed if products table exists
        if (!Schema::hasTable('products')) {
            return;
        }

        // ensure columns exist
        $hasSku = Schema::hasColumn('products', 'sku');
        $hasSlug = Schema::hasColumn('products', 'slug');
        $hasCategory = Schema::hasColumn('products', 'category_id');
        $hasIsActive = Schema::hasColumn('products', 'is_active');

        if (!($hasSku || $hasSlug || $hasCategory || $hasIsActive)) {
            return;
        }

        // get existing index names from information_schema to avoid duplicates (MySQL only)
        $existingIndexes = [];
        if (DB::getDriverName() === 'mysql') {
            $database = DB::getDatabaseName();
            $rows = DB::select(
                'SELECT DISTINCT INDEX_NAME as name FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                [$database, 'products']
            );

            $existingIndexes = array_map(fn($r) => (string) $r->name, $rows);
        }

        // For sqlite, use PRAGMA to list indexes
        if (DB::getDriverName() === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('products')");
            $existingIndexes = array_map(fn($r) => (string) ($r->name ?? $r->idx_name ?? ''), $rows);
        }

        Schema::table('products', function (Blueprint $table) use ($hasSku, $hasSlug, $hasCategory, $hasIsActive, $existingIndexes) {
            if ($hasSku) {
                $need = !in_array('products_sku_unique', $existingIndexes, true) && !in_array('sku_unique', $existingIndexes, true) && !in_array('sku', $existingIndexes, true);
                if ($need) {
                    $table->unique('sku');
                }
            }

            if ($hasSlug) {
                $need = !in_array('products_slug_unique', $existingIndexes, true) && !in_array('slug_unique', $existingIndexes, true) && !in_array('slug', $existingIndexes, true);
                if ($need) {
                    $table->unique('slug');
                }
            }

            if ($hasCategory) {
                $need = !in_array('products_category_id_index', $existingIndexes, true) && !in_array('category_id_index', $existingIndexes, true) && !in_array('category_id', $existingIndexes, true);
                if ($need) {
                    $table->index('category_id');
                }
            }

            if ($hasIsActive) {
                $need = !in_array('products_is_active_index', $existingIndexes, true) && !in_array('is_active_index', $existingIndexes, true) && !in_array('is_active', $existingIndexes, true);
                if ($need) {
                    $table->index('is_active');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try { $table->dropUnique(['sku']); } catch (\Throwable $e) {}
            try { $table->dropUnique(['slug']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['category_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['is_active']); } catch (\Throwable $e) {}
        });
    }
};
