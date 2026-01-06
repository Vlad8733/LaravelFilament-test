<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Check if an index exists on a table (cross-database compatible)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('$table')");
            foreach ($indexes as $index) {
                if ($index->name === $indexName) {
                    return true;
                }
            }
            return false;
        }
        
        // MySQL / MariaDB
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $result = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$indexName]);
            return count($result) > 0;
        }
        
        // PostgreSQL
        if ($driver === 'pgsql') {
            $result = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
            return count($result) > 0;
        }
        
        // Fallback: assume it doesn't exist
        return false;
    }

    public function up(): void
    {
        if (Schema::hasTable('cart_items')) {
            // First, check if the new unique index already exists
            $indexExists = $this->indexExists('cart_items', 'cart_items_user_product_variant_unique');
            
            if (!$indexExists) {
                Schema::table('cart_items', function (Blueprint $table) {
                    // Add variant_id column if not exists
                    if (!Schema::hasColumn('cart_items', 'variant_id')) {
                        $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
                    }
                });
                
                // Try to drop old unique index if it exists and is not needed by FK
                try {
                    $oldIndexExists = $this->indexExists('cart_items', 'cart_items_user_id_product_id_unique');
                    
                    if ($oldIndexExists) {
                        // Drop foreign keys that might depend on this index first
                        Schema::table('cart_items', function (Blueprint $table) {
                            try {
                                $table->dropForeign(['user_id']);
                            } catch (\Exception $e) {}
                        });
                        
                        Schema::table('cart_items', function (Blueprint $table) {
                            try {
                                $table->dropUnique('cart_items_user_id_product_id_unique');
                            } catch (\Exception $e) {}
                        });
                        
                        // Re-add foreign key
                        Schema::table('cart_items', function (Blueprint $table) {
                            try {
                                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                            } catch (\Exception $e) {}
                        });
                    }
                } catch (\Exception $e) {
                    // Ignore errors, the unique might not exist or be different
                }
                
                // Create new composite unique index
                Schema::table('cart_items', function (Blueprint $table) {
                    try {
                        $table->unique(['user_id', 'product_id', 'variant_id'], 'cart_items_user_product_variant_unique');
                    } catch (\Exception $e) {
                        // Index might already exist
                    }
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                try {
                    $table->dropUnique('cart_items_user_product_variant_unique');
                } catch (\Exception $e) {}
            });
        }
    }
};
