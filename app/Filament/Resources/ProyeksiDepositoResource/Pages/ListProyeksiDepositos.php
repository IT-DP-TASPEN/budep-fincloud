<?php

namespace App\Filament\Resources\ProyeksiDepositoResource\Pages;

use App\Filament\Resources\ProyeksiDepositoResource;
use App\Models\MasterDeposito;
use App\Models\proyeksi_deposito;
use App\Models\ProyeksiDeposito;
use App\Models\RekeningTransfer;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ListProyeksiDepositos extends ListRecords
{
    protected static string $resource = ProyeksiDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateProyeksi')
                ->label('Tarik Proyeksi Deposito')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('tanggal_awal')->label('Tanggal Awal')->required(),
                        DatePicker::make('tanggal_akhir')->label('Tanggal Akhir')->required(),
                        Checkbox::make('fallback_31_ke_30')
                            ->label('Tarik juga data tgl 31 jika tidak ada')
                            ->default(true),
                    ]),
                ])
                ->action(function (array $data) {
                    $tanggalAwal = Carbon::parse($data['tanggal_awal']);
                    $tanggalAkhir = Carbon::parse($data['tanggal_akhir']);

                    // list hari (1..31) yang perlu dicek
                    $daysToCheck = collect(CarbonPeriod::create($tanggalAwal, $tanggalAkhir))
                        ->map(fn($date) => (int) $date->format('d'))
                        ->unique()
                        ->values()
                        ->toArray();

                    // fallback 31 <-> 30
                    if ($data['fallback_31_ke_30']) {
                        if (!in_array(31, $daysToCheck)) {
                            $daysToCheck[] = 31;
                        } elseif (in_array(31, $daysToCheck)) {
                            $daysToCheck[] = 30;
                        }
                    }

                    // counters
                    $inserted = 0;
                    $skipped = 0;
                    $errors = 0;

                    DB::transaction(function () use ($daysToCheck, $tanggalAkhir, &$inserted, &$skipped, &$errors) {
                        proyeksi_deposito::truncate();

                        $deposits = MasterDeposito::where('status', 'Active')->get();

                        foreach ($deposits as $item) {
                            $nominal = (float) ($item->nominal ?? 0);
                            $sukuBunga = (float) ($item->bunga ?? 0);

                            $tglJatuhTempo = Carbon::parse($item->tgl_jatuh_tempo ?? $tanggalAkhir);

                            $dayValue = (int) ($item->tgl_bayar ?? $tglJatuhTempo->format('d'));

                            if (!in_array($dayValue, $daysToCheck)) {
                                $skipped++;
                                continue;
                            }

                            $safeDay = min($dayValue, $tglJatuhTempo->daysInMonth);

                            $tglBayar = $tglJatuhTempo->copy()->day($safeDay);

                            $tglSebelumnya = $tglBayar->copy()->subMonthNoOverflow();

                            $jumlahHari = $tglSebelumnya->diffInDays($tglBayar);

                            $nilaiBunga = (($nominal * $sukuBunga / 100) / 365) * $jumlahHari;

                            $pajak = $item->potong_pajak === 'Yes' ? round($nilaiBunga * 0.2) : 0;

                            $totalBayar = round($nilaiBunga - $pajak);

                            $rekening = RekeningTransfer::where('norek_deposito', $item->norek_deposito)
                                ->first();

                            try {
                                proyeksi_deposito::create([
                                    'rek_deposito' => $item->norek_deposito,
                                    'cif' => $item->cif_no,
                                    'nama_nasabah' => $item->nama_nasabah,
                                    'jangka_waktu' => $jumlahHari,
                                    'nominal' => $nominal,
                                    'total_bunga' => round($nilaiBunga),
                                    'total_pajak' => $pajak,
                                    'total_bayar' => $totalBayar,
                                    'tanggal_bayar' => str_pad($safeDay, 2, '0', STR_PAD_LEFT),
                                    'jatuh_tempo' => $tglBayar->format('Y-m-d'),
                                    'rekening_transfer_id' => optional($rekening)->id,
                                ]);

                                $inserted++;
                            } catch (\Throwable $e) {
                                $errors++;
                                Log::warning('Gagal simpan proyeksi untuk norek: ' . ($item->norek_deposito ?? 'n/a'), [
                                    'error' => $e->getMessage(),
                                    'norek' => $item->norek_deposito ?? null,
                                ]);
                                continue;
                            }
                        }
                    });

                    $body = "Selesai: inserted={$inserted}, skipped={$skipped}, errors={$errors}.";

                    Notification::make()
                        ->title('Penarikan Proyeksi Deposito Selesai')
                        ->body($body)
                        ->success()
                        ->send();

                    Log::info('Proyeksi Deposito generate summary', compact('inserted', 'skipped', 'errors'));
                }),

            // Actions\Action::make('clearData')
            //     ->label('Hapus Semua Data')
            //     ->icon('heroicon-o-trash')
            //     ->color('danger')
            //     ->requiresConfirmation()
            //     ->action(function () {
            //         proyeksi_deposito::truncate();

            //         Notification::make()
            //             ->title('Data Proyeksi Dihapus')
            //             ->body('Seluruh data proyeksi deposito telah dihapus.')
            //             ->success()
            //             ->send();
            //     }),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Simpan data CIF baru ke tabel Master CIF
        MasterCif::firstOrCreate(
            ['cif' => $data['cif']],
            [
                'customer_name' => $data['customer_name'] ?? '-',
                'no_hp' => $data['no_hp'] ?? '-',
                'no_ktp' => $data['no_ktp'] ?? '-',
                'status' => 'AKTIF',
                'register_date' => now(),
            ]
        );

        // Hapus kolom non-transfer agar tidak error
        unset($data['customer_name'], $data['no_hp'], $data['no_ktp']);

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('✅ Data berhasil disimpan!')
            ->body('CIF dan Rekening Transfer telah ditambahkan.')
            ->success()
            ->send();
    }
}
