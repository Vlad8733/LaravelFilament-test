<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Проверяем, есть ли поле slug
        if (!Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        // Обновляем существующие продукты без slug
        $products = DB::table('products')->whereNull('slug')->orWhere('slug', '')->get();
        
        foreach ($products as $product) {
            $slug = Str::slug($product->name);
            
            // Проверяем уникальность slug
            $counter = 1;
            $originalSlug = $slug;
            while (DB::table('products')->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            DB::table('products')
                ->where('id', $product->id)
                ->update(['slug' => $slug]);
        }
        
        // Проверяем, есть ли уже уникальный индекс
        $indexes = DB::select("PRAGMA index_list(products)");
        $hasUniqueSlugIndex = false;
        
        foreach ($indexes as $index) {
            if ($index->name === 'products_slug_unique') {
                $hasUniqueSlugIndex = true;
                break;
            }
        }
        
        // Создаем уникальный индекс только если его нет
        if (!$hasUniqueSlugIndex) {
            Schema::table('products', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
        
        // Делаем поле NOT NULL если оно nullable
        $columns = DB::select("PRAGMA table_info(products)");
        $slugColumn = collect($columns)->firstWhere('name', 'slug');
        
        if ($slugColumn && $slugColumn->notnull == 0) {
            // Для SQLite нужно пересоздать таблицу для изменения nullable
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        // Проверяем есть ли индекс перед удалением
        $indexes = DB::select("PRAGMA index_list(products)");
        $hasUniqueSlugIndex = false;
        
        foreach ($indexes as $index) {
            if ($index->name === 'products_slug_unique') {
                $hasUniqueSlugIndex = true;
                break;
            }
        }
        
        if ($hasUniqueSlugIndex) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique(['slug']);
            });
        }
    }
};
