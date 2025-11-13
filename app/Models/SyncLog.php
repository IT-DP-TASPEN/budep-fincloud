<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // ✅ tambahkan ini

class SyncLog extends Model
{
    protected $fillable = [
        'user_id',
        'cif_no',
        'response',
        'status',
        'ip_address',
        'mac_address',
        'sync_date',
        'description',
    ];

    protected $casts = [
        'sync_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
