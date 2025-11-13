<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekeningTransfer extends Model
{
    protected $fillable = [
        'norek_deposito',
        'cif',
        'norek_tujuan',
        'bank_tujuan',
        'kode_bank',
        'nama_rekening',
        'user_id',
    ];

    public function proyeksiDepositos()
    {
        return $this->hasMany(proyeksi_deposito::class, 'rek_deposito', 'norek_deposito');
    }

    public function masterCif()
    {
        return $this->belongsTo(\App\Models\MasterCif::class, 'cif', 'cif');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
