<?php

namespace App\Models;

use App\Models\RekeningTransfer;
use Illuminate\Database\Eloquent\Model;

class proyeksi_deposito extends Model
{
    protected $fillable = [
        'rek_deposito',
        'cif',
        'nama_nasabah',
        'jangka_waktu',
        'nominal',
        'total_bunga',
        'total_pajak',
        'total_bayar',
        'tanggal_bayar',
        'jatuh_tempo',
    ];

    public function rekeningTransfer()
    {
        return $this->belongsTo(RekeningTransfer::class, 'rek_deposito', 'norek_deposito');
    }
}
