<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('import_jobs') && ! Schema::hasColumn('import_jobs', 'mapping')) {
            Schema::table('import_jobs', function (Blueprint $table) {
                $table->json('mapping')->nullable()->after('file_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('import_jobs') && Schema::hasColumn('import_jobs', 'mapping')) {
            Schema::table('import_jobs', function (Blueprint $table) {
                $table->dropColumn('mapping');
            });
        }
    }
};
