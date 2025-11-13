<?php

namespace App\Imports;

use App\Models\MasterCif;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class MasterCifImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Helper untuk membersihkan tanda petik dan spasi
        $clean = fn($value) => is_string($value)
            ? trim(str_replace("'", '', $value))
            : $value;

        foreach ($rows as $row) {
            try {
                MasterCif::updateOrCreate(
                    [
                        'cif' => $clean($row['cif'] ?? null),
                    ],
                    [
                        'alt_no'         => $clean($row['alt_no'] ?? null),
                        'customer_name'  => $clean($row['customer_name'] ?? null),
                        'no_hp'          => $clean($row['no_hp'] ?? null),
                        'customer_type'  => $clean($row['customer_type'] ?? null),
                        'status'         => $clean($row['status'] ?? 'Active'),
                        'no_ktp'         => $clean($row['ktp'] ?? null),
                        'kode_cabang'    => $clean($row['kode_cabang'] ?? null),
                        'register_date'  => $this->parseDate($row['register_date'] ?? null),
                        'tgl_lahir'      => $this->parseDate($row['tgl_lahir'] ?? null),
                    ]
                );
            } catch (\Exception $e) {
                Log::error('❌ Gagal import CIF: ' . ($row['cif'] ?? '-') . ' | Error: ' . $e->getMessage());
            }
        }
    }

    private function parseDate($value)
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
