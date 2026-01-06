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
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем старое поле status если оно есть
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }

            // Добавляем связь с order_statuses
            $table->foreignId('order_status_id')->nullable()->after('id')->constrained()->nullOnDelete();

            // Поле для отслеживания
            $table->string('tracking_number')->nullable()->after('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['order_status_id']);
            $table->dropColumn(['order_status_id', 'tracking_number']);
        });
    }
};
