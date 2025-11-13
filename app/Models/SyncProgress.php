<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncProgress extends Model
{
    protected $fillable = [
        'process_name',
        'total',
        'processed',
        'status'
    ];
}
