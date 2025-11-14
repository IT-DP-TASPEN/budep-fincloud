<?php

namespace App\Filament\Resources\MasterDepositoResource\Pages;

use Filament\Actions;
use App\Models\SyncLog;
use App\Models\MasterCif;
use App\Models\SyncProgress;
use Filament\Actions\Action;
use App\Models\MasterDeposito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\MasterDepositoResource;
use App\Filament\Widgets\SyncDepositoProgressWidget;

class ListMasterDepositos extends ListRecords
{
    protected static string $resource = MasterDepositoResource::class;

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         SyncDepositoProgressWidget::class,
    //     ];
    // }

    protected function getHeaderActions(): array
    {
        return [

            // 🔹 Tombol Sync dari API
            Action::make('syncFromApi')
                ->label('Sync Data dari API Deposito')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mulai Sinkronisasi Deposito?')
                ->modalDescription('Proses ini akan mengambil data semua CIF dari tabel Master CIF.')
                ->modalSubmitActionLabel('Mulai Sync')
                ->action(function () {
                    // DEV
                    // $url = 'http://172.22.80.18:17000/v2/account/deposit/list';
                    // Prod
                    $url = 'http://172.20.57.5:17000/v2/account/deposit/list';
                    $cifs = ['00600000448','00700000178'];
                    // $cifs = MasterCif::pluck('cif')->toArray();

                    if (empty($cifs)) {
                        Notification::make()
                            ->title('Tidak Ada CIF ❌')
                            ->body('Tidak ditemukan CIF di tabel master_cifs.')
                            ->warning()
                            ->send();

                        return;
                    }

                    // 🔹 Reset atau buat progress
                    $progress = SyncProgress::updateOrCreate(
                        ['process_name' => 'deposito_sync'],
                        [
                            'total'     => count($cifs),
                            'processed' => 0,
                            'status'    => 'running',
                        ]
                    );

                    $total = count($cifs);
                    $success = 0;
                    $notFound = 0;
                    $emptyList = 0;
                    $httpError = 0;
                    $errors = 0;

                    // Batasi batch agar tidak timeout
                    $batchSize = 30;
                    $chunks = array_chunk($cifs, $batchSize);

                    foreach ($chunks as $batch) {
                        foreach ($batch as $cif) {
                            try {
                                $response = Http::timeout(20)->get($url, ['cifNo' => $cif]);
                                $responseData = $response->json();

                                $status = 'success';
                                $description = 'Data berhasil diambil.';

                                if ($response->failed()) {
                                    $status = 'http_error';
                                    $description = 'HTTP gagal: ' . $response->status();
                                    $httpError++;
                                } elseif (
                                    isset($responseData['responseCode']) &&
                                    in_array($responseData['responseCode'], ['40', '41', '404'])
                                ) {
                                    $status = 'not_found';
                                    $description = $responseData['description'] ?? 'Customer Not Found';
                                    $notFound++;
                                } elseif (
                                    !isset($responseData['list']) ||
                                    (isset($responseData['list']) && empty($responseData['list']))
                                ) {
                                    $status = 'empty';
                                    $description = 'Tidak ada data deposito dikembalikan.';
                                    $emptyList++;
                                }

                                // Simpan log
                                SyncLog::create([
                                    'user_id'     => auth()->id(),
                                    'cif_no'      => $cif,
                                    'response'    => json_encode($responseData, JSON_PRETTY_PRINT),
                                    'status'      => $status,
                                    'description' => $description,
                                    'ip_address'  => request()->ip(),
                                    'mac_address' => null,
                                    'sync_date'   => now(),
                                ]);

                                if ($status === 'success' && isset($responseData['list']) && is_array($responseData['list'])) {
                                    foreach ($responseData['list'] as $item) {
                                        $nominal = (float) ($item['ledgerBalance'] ?? 0);
                                        $potongPajak = $nominal > 7500000 ? 'Yes' : 'No';

                                        MasterDeposito::updateOrCreate(
                                            ['norek_deposito' => $item['id'] ?? null],
                                            [
                                                'cif_no'          => $item['cifNo'] ?? $cif,
                                                'nama_nasabah'    => $item['customerName'] ?? null,
                                                'product_name'    => $item['productCode'] ?? null,
                                                'account_type'    => $item['accountType'] ?? null,
                                                'currency'        => $item['currencyCode'] ?? null,
                                                'nominal'         => $nominal,
                                                'bunga'           => $item['intRate'] ?? 0,
                                                'kode_cabang'     => $item['branchCode'] ?? null,
                                                'kode_produk'     => $item['productCode'] ?? null,
                                                'tgl_mulai'       => $item['issueDate'] ?? null,
                                                'tgl_bayar'       => isset($item['maturityDate'])
                                                    ? str_pad(date('d', strtotime($item['maturityDate'])), 2, '0', STR_PAD_LEFT)
                                                    : null,
                                                'tgl_jatuh_tempo' => $item['maturityDate'] ?? null,
                                                'aro'             => $item['aro'] ?? null,
                                                'status'          => $item['status'] ?? null,
                                                'potong_pajak'    => $potongPajak,
                                            ]
                                        );
                                        $success++;
                                    }
                                }
                            } catch (\Throwable $e) {
                                Log::error("❌ Error sinkronisasi CIF {$cif}: {$e->getMessage()}");
                                SyncLog::create([
                                    'user_id'     => auth()->id(),
                                    'cif_no'      => $cif,
                                    'response'    => json_encode(['error' => $e->getMessage()]),
                                    'status'      => 'error',
                                    'description' => 'Exception saat sinkronisasi',
                                    'ip_address'  => request()->ip(),
                                    'mac_address' => null,
                                    'sync_date'   => now(),
                                ]);
                                $errors++;
                            }

                            $progress->increment('processed');
                        }

                        sleep(1);
                    }

                    $progress->update(['status' => 'completed']);

                    Notification::make()
                        ->title('Sinkronisasi Selesai ✅')
                        ->body("
                            Total CIF: {$total}\n
                            ✅ Berhasil: {$success}\n
                            ⚠️ Not Found: {$notFound}\n
                            🟡 Empty List: {$emptyList}\n
                            🔴 HTTP Error: {$httpError}\n
                            ❌ Exception: {$errors}
                        ")
                        ->success()
                        ->send();
                }),

            // 🔹 Tombol Kosongkan Tabel
            // Action::make('truncateTable')
            //     ->label('Kosongkan Tabel')
            //     ->icon('heroicon-o-trash')
            //     ->color('danger')
            //     ->requiresConfirmation()
            //     ->modalHeading('Yakin ingin mengosongkan tabel Master Deposito?')
            //     ->modalDescription('Seluruh data akan dihapus secara permanen dari database.')
            //     ->modalSubmitActionLabel('Ya, Hapus Semua')
            //     ->action(function () {
            //         try {
            //             DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            //             MasterDeposito::truncate();
            //             DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            //             Notification::make()
            //                 ->title('Tabel Dikosongkan ✅')
            //                 ->body('Seluruh data Master Deposito berhasil dihapus.')
            //                 ->success()
            //                 ->send();
            //         } catch (\Throwable $th) {
            //             Notification::make()
            //                 ->title('Gagal Mengosongkan ❌')
            //                 ->body('Terjadi kesalahan: ' . $th->getMessage())
            //                 ->danger()
            //                 ->send();
            //         }
            //     }),

            Actions\CreateAction::make(),
        ];
    }
}
