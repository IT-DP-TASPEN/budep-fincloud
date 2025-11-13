<?php

namespace App\Imports;

use App\Models\MasterCif;
use App\Models\Deposit;
use App\Models\TransferAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Row;
use Throwable;

class MasterDepositoImport implements OnEachRow, WithHeadingRow, WithChunkReading, SkipsOnError
{
    use Importable;

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row->toArray());

        Log::debug('🔹 [DEBUG] Row dibaca dari file CSV', ['rowIndex' => $rowIndex, 'raw' => $row]);

        try {
            DB::transaction(function () use ($row, $rowIndex) {

                $cifNumber = trim($row['cif_no'] ?? '');
                $customerName = trim($row['customer_name'] ?? '');
                $accountNumber = trim($row['account_no'] ?? '');

                // --- FIX scientific notation ---
                $accountNumber = preg_replace('/[^0-9]/', '', $accountNumber);

                if (empty($cifNumber) || empty($accountNumber)) {
                    throw new \Exception("CIF atau Nomor Rekening kosong pada baris {$rowIndex}");
                }

                Log::debug('📋 Nilai utama', [
                    'rowIndex' => $rowIndex,
                    'cif_no' => $cifNumber,
                    'account_no' => $accountNumber,
                    'customer_name' => $customerName,
                ]);

                // --- Konversi nilai numerik ---
                $interestRate = (float) str_replace(',', '.', ($row['interest_rate'] ?? 0));

                $nominalRaw = $row['nominal'] ?? 0;
                $nominal = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string) $nominalRaw));

                Log::debug('💰 Nilai konversi', [
                    'interest_rate' => $interestRate,
                    'nominal' => $nominal,
                ]);

                // --- FIX format tanggal (dd/mm/yyyy → yyyy-mm-dd) ---
                $startDate = self::parseDate($row['start_date'] ?? null);
                $maturityDate = self::parseDate($row['maturity_date'] ?? null);

                // Simpan Master CIF
                $cif = MasterCif::updateOrCreate(
                    ['cif_number' => $cifNumber],
                    ['full_name' => $customerName]
                );

                Log::info('✅ Berhasil updateOrCreate MasterCif', [
                    'id' => $cif->id,
                    'cif_number' => $cifNumber,
                    'full_name' => $customerName,
                ]);

                // Simpan Deposit
                Deposit::updateOrCreate(
                    ['account_number' => $accountNumber],
                    [
                        'master_cif_id' => $cif->id,
                        'deposit_amount' => $nominal,
                        'interest_rate' => $interestRate,
                        'start_date' => $startDate,
                        'maturity_date' => $maturityDate,
                        'product_id' => $row['product_id'] ?? null,
                        'product_name' => $row['product_name'] ?? null,
                        'certificate_no' => $row['certificate_no'] ?? null,
                        'status' => 'active',
                    ]
                );

                Log::info('✅ Berhasil updateOrCreate Deposit', [
                    'rekening' => $accountNumber,
                    'start_date' => $startDate,
                    'maturity_date' => $maturityDate,
                ]);

                // Simpan Transfer Account
                TransferAccount::updateOrCreate(
                    ['account_number' => $accountNumber],
                    [
                        'transfer_account_number' => $accountNumber,
                        'bank_name' => $row['bank_name'] ?? null,
                        'cif_no' => $cifNumber,
                        'account_holder_name' => $customerName,
                    ]
                );

                Log::info('✅ Berhasil updateOrCreate TransferAccount', [
                    'rekening' => $accountNumber,
                ]);
            });
        } catch (Throwable $th) {
            Log::error('💥 Gagal import baris MasterDeposito', [
                'rowIndex' => $rowIndex,
                'row' => $row,
                'error' => $th->getMessage(),
            ]);

            throw $th;
        }
    }

    /**
     * Konversi format tanggal dd/mm/yyyy → yyyy-mm-dd
     */
    private static function parseDate(?string $value): ?string
    {
        if (empty($value)) return null;

        // jika format seperti "20/10/2025"
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
            [$d, $m, $y] = explode('/', $value);
            return sprintf('%04d-%02d-%02d', $y, $m, $d);
        }

        // jika sudah format yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        return null;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function onError(Throwable $e)
    {
        Log::error('💥 Error global saat import MasterDeposito: ' . $e->getMessage());
    }
}
