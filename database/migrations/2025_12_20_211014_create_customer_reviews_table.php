<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Рейтинги по аспектам (1-5 звёзд)
            $table->tinyInteger('delivery_rating')->unsigned();
            $table->tinyInteger('packaging_rating')->unsigned();
            $table->tinyInteger('product_rating')->unsigned();

            // Агрегированный рейтинг
            $table->decimal('overall_rating', 2, 1);

            // Текстовый отзыв
            $table->text('comment')->nullable();

            // Модерация
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('moderation_notes')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('moderated_at')->nullable();

            $table->timestamps();

            // Один отзыв на продукт в заказе
            $table->unique(['order_id', 'product_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_reviews');
    }
};
