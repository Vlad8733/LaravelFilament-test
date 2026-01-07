<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Payment methods - stores ONLY tokens/masks, no sensitive data
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // card, paypal, bank_transfer
            $table->string('provider')->nullable(); // stripe, paypal, etc.
            $table->string('token')->nullable(); // payment gateway token (not card number!)
            $table->string('last_four', 4)->nullable(); // last 4 digits for display
            $table->string('brand')->nullable(); // visa, mastercard, amex
            $table->string('holder_name')->nullable();
            $table->string('expiry_month', 2)->nullable();
            $table->string('expiry_year', 4)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_expired')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
