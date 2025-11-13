<?php

namespace App\Imports;

use App\Models\RekeningTransfer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RekeningTransferImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $bankCodes = [
            "MANDIRI" => "BMRIIDJA",
            "MANTAP" => "SIHBIDJ1",
            "CIMB" => "BNIAIDJA",
            "BANK JATENG" => "SYJGIDJ1",
            "BANK JATIM" => "PDJTIDJ1",
            "BPD SULTRA" => "PDWRIDJ1",
            "BSI" => "BSMDIDJA",
            "BTN" => "BTANIDJA",
            "BTPN" => "SUNIIDJA",
            "BWS" => "BSDRIDJA",
            "DANAMON" => "BDINIDJA",
            "DKI" => "BDKIIDJ1",
            "BANK DKI" => "BDKIIDJ1",
            "DKI SYARIAH" => "SYDKIDJ1",
            "MAYBANK" => "IBBKIDJA",
            "MEGA" => "MEGAIDJA",
            "OCBC" => "NISPIDJA",
            "PANIN" => "PINBIDJA",
            "PERMATA" => "BBBAIDJA",
            "SINARMAS" => "SBJKIDJA",
            "BCA" => "CENAIDJA",
            "BNI" => "BNINIDJA",
            "BJB" => "PDJBIDJA",
            "BANK BJB" => "PDJBIDJA",
            "BJB SYARIAH" => "SYJBIDJ1",
            "MUAMALAT" => "MUABIDJA",
            "ALLO BANK" => "ALOBIDJA",
            "BLU BCA" => "ROYBIDJ1",
            "SEABANK" => "KSEBIDJ1",
            "BANK JAGO" => "JAGBIDJA",
            "SMBC" => "SUNIIDJA",
        ];

        foreach ($rows as $row) {
            try {
                $bankName = strtoupper(trim($row['bank_tujuan'] ?? ''));

                $kodeBank = $bankCodes[$bankName] ?? null;

                RekeningTransfer::updateOrCreate(
                    ['norek_deposito' => $row['norek_deposito']],
                    [
                        'cif'           => $row['cif'] ?? null,
                        'norek_tujuan'  => $row['norek_tujuan'] ?? null,
                        'bank_tujuan'   => $row['bank_tujuan'] ?? null,
                        'kode_bank'     => $kodeBank,
                        'nama_rekening' => $row['nama_rekening'] ?? null,
                    ]
                );
            } catch (\Exception $e) {
                Log::error("❌ Gagal import rekening deposito {$row['norek_deposito']}: " . $e->getMessage());
            }
        }
    }
}
