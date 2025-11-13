<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDeposito extends Model
{
    use HasFactory;

    protected $fillable = [
        'cif_no',
        'norek_deposito',
        'nama_nasabah',
        'product_name',
        'account_type',
        'currency',
        'nominal',
        'bunga',
        'kode_cabang',
        'kode_produk',
        'tgl_mulai',
        'tgl_bayar',
        'tgl_jatuh_tempo',
        'aro',
        'potong_pajak',
        'status',
    ];
}
