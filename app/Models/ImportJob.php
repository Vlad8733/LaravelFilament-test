<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportJob extends Model
{
    use HasFactory;

    protected $table = 'import_jobs';

    protected $fillable = [
        'uuid',
        'user_id',
        'file_path',
        'mapping',
        'failed_file_path',
        'total_rows',
        'processed_rows',
        'failed_count',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'failed_count' => 'integer',
        'mapping' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
