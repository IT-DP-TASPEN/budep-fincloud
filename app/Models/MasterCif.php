<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCif extends Model
{
    use HasFactory;

    protected $table = 'master_cifs';

    protected $fillable = [
        'cif',
        'alt_no',
        'customer_name',
        'no_hp',
        'customer_type',
        'no_ktp',
        'kode_cabang',
        'register_date',
        'status',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->cif_number} - {$this->name}";
    }
}
