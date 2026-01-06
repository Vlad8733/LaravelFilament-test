<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateProductsSlugsSeeder extends Seeder
{
    public function run(): void
    {
        // Обновляем продукты без slug
        $products = DB::table('products')->whereNull('slug')->orWhere('slug', '')->get();

        foreach ($products as $product) {
            $slug = Str::slug($product->name);

            // Проверяем уникальность slug
            $counter = 1;
            $originalSlug = $slug;
            while (DB::table('products')->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $originalSlug.'-'.$counter;
                $counter++;
            }

            DB::table('products')
                ->where('id', $product->id)
                ->update(['slug' => $slug]);
        }

        $this->command->info('Products slugs updated successfully!');
    }
}
