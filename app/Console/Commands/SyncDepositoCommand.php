<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterCif;
use App\Models\MasterDeposito;
use App\Models\SyncLog;
use App\Models\SyncProgress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncDepositoCommand extends Command
{
    protected $signature = 'sync:deposito';
    protected $description = 'Sinkronisasi data deposito dari API';

    public function handle()
    {
        Log::info('🚀 Memulai proses sync deposito...');

        $cifs = MasterCif::pluck('cif')->toArray();

        if (empty($cifs)) {
            Log::warning('❌ Tidak ada data CIF ditemukan di tabel master_cifs.');
            $this->error('Tidak ada data CIF ditemukan.');
            return Command::FAILURE;
        }

        // Jika sudah ada proses sebelumnya, lanjutkan dari situ
        $progress = SyncProgress::updateOrCreate(
            ['process_name' => 'deposito_sync'],
            ['total' => count($cifs), 'status' => 'running']
        );

        $progress->update(['processed' => 0]);

        $url = 'http://172.22.80.18:17000/v2/account/deposit/list';
        $success = 0;
        $failed = 0;
        $notFound = 0;

        foreach ($cifs as $index => $cif) {
            $progress->refresh();

            if ($progress->status === 'paused') {
                Log::info("⏸️ Dijeda di CIF {$cif}");
                sleep(3);
                continue;
            }

            if (in_array($progress->status, ['stopped', 'failed'])) {
                Log::warning("🛑 Sync dihentikan oleh user di {$cif}");
                $progress->update(['status' => 'stopped']);
                break;
            }

            try {
                Log::info("🔹 [{$index}/{$progress->total}] Memproses CIF {$cif}");

                $response = Http::timeout(20)->get($url, ['cifNo' => $cif]);
                $data = $response->json();

                $status = 'success';
                $description = 'Data berhasil diambil.';

                if ($response->failed()) {
                    $status = 'http_error';
                    $description = 'HTTP gagal: ' . $response->status();
                    $failed++;
                } elseif (
                    isset($data['responseCode']) &&
                    in_array($data['responseCode'], ['40', '41', '404'])
                ) {
                    $status = 'not_found';
                    $description = $data['description'] ?? 'Customer not found';
                    $notFound++;
                }

                // Simpan log ke tabel
                SyncLog::create([
                    'user_id'     => 1,
                    'cif_no'      => $cif,
                    'response'    => json_encode($data, JSON_PRETTY_PRINT),
                    'status'      => $status,
                    'ip_address'  => request()?->ip(),
                    'mac_address' => null,
                    'sync_date'   => now(),
                    'description' => $description ?? null,
                ]);

                // Hanya update DB kalau sukses
                if ($status === 'success' && isset($data['list']) && is_array($data['list'])) {
                    foreach ($data['list'] as $item) {
                        MasterDeposito::updateOrCreate(
                            ['norek_deposito' => $item['id'] ?? null],
                            [
                                'cif_no'          => $item['cifNo'] ?? $cif,
                                'nama_nasabah'    => $item['customerName'] ?? null,
                                'product_name'    => $item['productCode'] ?? null,
                                'account_type'    => $item['accountType'] ?? null,
                                'currency'        => $item['currencyCode'] ?? null,
                                'nominal'         => $item['ledgerBalance'] ?? 0,
                                'bunga'           => $item['intRate'] ?? 0,
                                'tgl_mulai'       => $item['issueDate'] ?? null,
                                'tgl_jatuh_tempo' => $item['maturityDate'] ?? null,
                                'aro'             => $item['aro'] ?? null,
                                'status'          => $item['status'] ?? null,
                            ]
                        );
                        $success++;
                    }
                }

                Log::info("✅ CIF {$cif} selesai diproses (status: {$status})");
            } catch (\Throwable $e) {
                $failed++;
                Log::error("❌ Gagal memproses CIF {$cif}: {$e->getMessage()}");
                SyncLog::create([
                    'user_id' => 1,
                    'cif_no' => $cif,
                    'response' => json_encode(['error' => $e->getMessage()]),
                    'status' => 'error',
                    'sync_date' => now(),
                ]);
            }

            $progress->increment('processed');
            sleep(1); // jeda biar gak overload
        }

        $progress->update(['status' => 'completed']);
        Log::info('🎯 Sinkronisasi deposito selesai', [
            'success' => $success,
            'failed' => $failed,
            'not_found' => $notFound,
            'progress_id' => $progress->id,
        ]);

        $this->info("Sinkronisasi selesai ✅ Total CIF: {$progress->total}, Berhasil: {$success}, Gagal: {$failed}");
        return Command::SUCCESS;
    }
}
