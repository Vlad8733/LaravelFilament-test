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
        Schema::table('product_chats', function (Blueprint $table) {
            $table->unique(['product_id', 'customer_id', 'seller_id'], 'unique_product_chat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_chats', function (Blueprint $table) {
            $table->dropUnique('unique_product_chat');
        });
    }
};
