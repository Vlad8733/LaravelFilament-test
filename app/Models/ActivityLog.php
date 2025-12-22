<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'subject_type',
        'subject_id',
        'created_at',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function getCreatedAtAttribute($value)
    {
        return \Illuminate\Support\Carbon::parse($value);
    }
}
