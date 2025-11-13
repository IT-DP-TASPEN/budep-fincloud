<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDeposito extends Model
{
    use SoftDeletes;

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
        'norek_tujuan',
        'bank_tujuan',
        'kode_bank',
        'nama_rekening',
        'emailcorporate',
        'ibuobu',
        'currency',
        'remark1',
        'remark2',
        'remark3',
        'adjust1',
        'adjust2',
        'adjust3',
        'adjust4',
        'adjust5',
        'adjust6',
        'adjust7',
        'adjust8',
        'adjust9',
        'adjust10',
        'adjust11',
        'adjust12',
        'adjust13',
    ];
}
