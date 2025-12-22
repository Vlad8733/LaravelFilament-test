<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('import_jobs', 'failed_file_path')) {
            Schema::table('import_jobs', function (Blueprint $table) {
                $table->string('failed_file_path')->nullable()->after('failed_count');
            });
        }
    }

    public function down(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->dropColumn('failed_file_path');
        });
    }
};
