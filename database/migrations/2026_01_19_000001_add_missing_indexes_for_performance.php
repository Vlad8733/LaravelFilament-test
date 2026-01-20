<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing indexes for better query performance.
 *
 * These indexes are based on foreign key relationships and common query patterns
 * identified during the code audit.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Orders table indexes
        if (Schema::hasTable('orders')) {
            $this->addIndexSafely('orders', 'user_id', 'orders_user_id_index');
            $this->addIndexSafely('orders', 'order_status_id', 'orders_order_status_id_index');
            $this->addIndexSafely('orders', 'payment_status', 'orders_payment_status_index');
            $this->addIndexSafely('orders', 'coupon_code', 'orders_coupon_code_index');
            $this->addIndexSafely('orders', 'created_at', 'orders_created_at_index');
        }

        // Reviews table indexes
        if (Schema::hasTable('reviews')) {
            $this->addIndexSafely('reviews', 'user_id', 'reviews_user_id_index');
            $this->addIndexSafely('reviews', 'product_id', 'reviews_product_id_index');
            $this->addIndexSafely('reviews', 'is_approved', 'reviews_is_approved_index');
        }

        // Customer reviews table indexes
        if (Schema::hasTable('customer_reviews')) {
            $this->addIndexSafely('customer_reviews', 'user_id', 'customer_reviews_user_id_index');
            $this->addIndexSafely('customer_reviews', 'product_id', 'customer_reviews_product_id_index');
            $this->addIndexSafely('customer_reviews', 'order_id', 'customer_reviews_order_id_index');
            $this->addIndexSafely('customer_reviews', 'status', 'customer_reviews_status_index');
        }

        // Coupons table indexes
        if (Schema::hasTable('coupons')) {
            $this->addIndexSafely('coupons', 'is_active', 'coupons_is_active_index');
            $this->addIndexSafely('coupons', 'expires_at', 'coupons_expires_at_index');
            $this->addIndexSafely('coupons', 'code', 'coupons_code_index');
        }

        // Activity logs table indexes (polymorphic relation)
        if (Schema::hasTable('activity_logs')) {
            $this->addIndexSafely('activity_logs', 'user_id', 'activity_logs_user_id_index');
            $this->addIndexSafely('activity_logs', 'created_at', 'activity_logs_created_at_index');
            $this->addCompoundIndexSafely('activity_logs', ['subject_type', 'subject_id'], 'activity_logs_subject_index');
        }

        // Refund requests table indexes
        if (Schema::hasTable('refund_requests')) {
            $this->addIndexSafely('refund_requests', 'user_id', 'refund_requests_user_id_index');
            $this->addIndexSafely('refund_requests', 'order_id', 'refund_requests_order_id_index');
            $this->addIndexSafely('refund_requests', 'status', 'refund_requests_status_index');
        }

        // Wishlist items table indexes
        if (Schema::hasTable('wishlist_items')) {
            $this->addIndexSafely('wishlist_items', 'user_id', 'wishlist_items_user_id_index');
            $this->addIndexSafely('wishlist_items', 'product_id', 'wishlist_items_product_id_index');
        }

        // Cart items table indexes
        if (Schema::hasTable('cart_items')) {
            $this->addIndexSafely('cart_items', 'user_id', 'cart_items_user_id_index');
            $this->addIndexSafely('cart_items', 'session_id', 'cart_items_session_id_index');
        }

        // Social accounts table indexes
        if (Schema::hasTable('social_accounts')) {
            $this->addIndexSafely('social_accounts', 'user_id', 'social_accounts_user_id_index');
            $this->addCompoundIndexSafely('social_accounts', ['provider', 'provider_id'], 'social_accounts_provider_index');
        }

        // Login histories table indexes
        if (Schema::hasTable('login_histories')) {
            $this->addIndexSafely('login_histories', 'user_id', 'login_histories_user_id_index');
            $this->addIndexSafely('login_histories', 'created_at', 'login_histories_created_at_index');
        }

        // Payment methods table indexes
        if (Schema::hasTable('payment_methods')) {
            $this->addIndexSafely('payment_methods', 'user_id', 'payment_methods_user_id_index');
        }

        // Product chats table indexes
        if (Schema::hasTable('product_chats')) {
            $this->addIndexSafely('product_chats', 'customer_id', 'product_chats_customer_id_index');
            $this->addIndexSafely('product_chats', 'seller_id', 'product_chats_seller_id_index');
            $this->addIndexSafely('product_chats', 'product_id', 'product_chats_product_id_index');
        }

        // Product chat messages table indexes
        if (Schema::hasTable('product_chat_messages')) {
            $this->addIndexSafely('product_chat_messages', 'product_chat_id', 'product_chat_messages_chat_id_index');
            $this->addCompoundIndexSafely('product_chat_messages', ['is_seller', 'is_read'], 'product_chat_messages_unread_index');
        }

        // Tickets table indexes
        if (Schema::hasTable('tickets')) {
            $this->addIndexSafely('tickets', 'user_id', 'tickets_user_id_index');
            $this->addIndexSafely('tickets', 'status', 'tickets_status_index');
            $this->addIndexSafely('tickets', 'priority', 'tickets_priority_index');
        }

        // Companies table indexes
        if (Schema::hasTable('companies')) {
            $this->addIndexSafely('companies', 'user_id', 'companies_user_id_index');
            $this->addIndexSafely('companies', 'is_verified', 'companies_is_verified_index');
        }

        // Company follows table indexes
        if (Schema::hasTable('company_follows')) {
            $this->addIndexSafely('company_follows', 'user_id', 'company_follows_user_id_index');
            $this->addIndexSafely('company_follows', 'company_id', 'company_follows_company_id_index');
        }

        // Import jobs table indexes
        if (Schema::hasTable('import_jobs')) {
            $this->addIndexSafely('import_jobs', 'user_id', 'import_jobs_user_id_index');
            $this->addIndexSafely('import_jobs', 'status', 'import_jobs_status_index');
        }

        // Webhooks table indexes
        if (Schema::hasTable('webhooks')) {
            $this->addIndexSafely('webhooks', 'is_active', 'webhooks_is_active_index');
        }
    }

    public function down(): void
    {
        // Orders
        $this->dropIndexSafely('orders', 'orders_user_id_index');
        $this->dropIndexSafely('orders', 'orders_order_status_id_index');
        $this->dropIndexSafely('orders', 'orders_payment_status_index');
        $this->dropIndexSafely('orders', 'orders_coupon_code_index');
        $this->dropIndexSafely('orders', 'orders_created_at_index');

        // Reviews
        $this->dropIndexSafely('reviews', 'reviews_user_id_index');
        $this->dropIndexSafely('reviews', 'reviews_product_id_index');
        $this->dropIndexSafely('reviews', 'reviews_is_approved_index');

        // Customer reviews
        $this->dropIndexSafely('customer_reviews', 'customer_reviews_user_id_index');
        $this->dropIndexSafely('customer_reviews', 'customer_reviews_product_id_index');
        $this->dropIndexSafely('customer_reviews', 'customer_reviews_order_id_index');
        $this->dropIndexSafely('customer_reviews', 'customer_reviews_status_index');

        // Coupons
        $this->dropIndexSafely('coupons', 'coupons_is_active_index');
        $this->dropIndexSafely('coupons', 'coupons_expires_at_index');
        $this->dropIndexSafely('coupons', 'coupons_code_index');

        // Activity logs
        $this->dropIndexSafely('activity_logs', 'activity_logs_user_id_index');
        $this->dropIndexSafely('activity_logs', 'activity_logs_created_at_index');
        $this->dropIndexSafely('activity_logs', 'activity_logs_subject_index');

        // Other tables - similar pattern
        $this->dropIndexSafely('refund_requests', 'refund_requests_user_id_index');
        $this->dropIndexSafely('refund_requests', 'refund_requests_order_id_index');
        $this->dropIndexSafely('refund_requests', 'refund_requests_status_index');

        $this->dropIndexSafely('wishlist_items', 'wishlist_items_user_id_index');
        $this->dropIndexSafely('wishlist_items', 'wishlist_items_product_id_index');

        $this->dropIndexSafely('cart_items', 'cart_items_user_id_index');
        $this->dropIndexSafely('cart_items', 'cart_items_session_id_index');

        $this->dropIndexSafely('social_accounts', 'social_accounts_user_id_index');
        $this->dropIndexSafely('social_accounts', 'social_accounts_provider_index');

        $this->dropIndexSafely('login_histories', 'login_histories_user_id_index');
        $this->dropIndexSafely('login_histories', 'login_histories_created_at_index');

        $this->dropIndexSafely('payment_methods', 'payment_methods_user_id_index');

        $this->dropIndexSafely('product_chats', 'product_chats_customer_id_index');
        $this->dropIndexSafely('product_chats', 'product_chats_seller_id_index');
        $this->dropIndexSafely('product_chats', 'product_chats_product_id_index');

        $this->dropIndexSafely('product_chat_messages', 'product_chat_messages_chat_id_index');
        $this->dropIndexSafely('product_chat_messages', 'product_chat_messages_unread_index');

        $this->dropIndexSafely('tickets', 'tickets_user_id_index');
        $this->dropIndexSafely('tickets', 'tickets_status_index');
        $this->dropIndexSafely('tickets', 'tickets_priority_index');

        $this->dropIndexSafely('companies', 'companies_user_id_index');
        $this->dropIndexSafely('companies', 'companies_is_verified_index');

        $this->dropIndexSafely('company_follows', 'company_follows_user_id_index');
        $this->dropIndexSafely('company_follows', 'company_follows_company_id_index');

        $this->dropIndexSafely('import_jobs', 'import_jobs_user_id_index');
        $this->dropIndexSafely('import_jobs', 'import_jobs_status_index');

        $this->dropIndexSafely('webhooks', 'webhooks_is_active_index');
    }

    /**
     * Add a single column index if it doesn't exist
     */
    private function addIndexSafely(string $table, string $column, string $indexName): void
    {
        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
            $table->index($column, $indexName);
        });
    }

    /**
     * Add a compound index if it doesn't exist
     */
    private function addCompoundIndexSafely(string $table, array $columns, string $indexName): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    /**
     * Drop an index if it exists
     */
    private function dropIndexSafely(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        if (! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $database = DB::getDatabaseName();
            $result = DB::select(
                'SELECT COUNT(*) as cnt FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$database, $table, $indexName]
            );

            return $result[0]->cnt > 0;
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");
            foreach ($indexes as $index) {
                if (($index->name ?? '') === $indexName) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'pgsql') {
            $result = DB::select(
                'SELECT COUNT(*) as cnt FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $indexName]
            );

            return $result[0]->cnt > 0;
        }

        return false;
    }
};
