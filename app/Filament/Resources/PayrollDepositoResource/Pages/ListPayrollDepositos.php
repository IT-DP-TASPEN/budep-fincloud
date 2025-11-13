<?php

namespace App\Filament\Resources\PayrollDepositoResource\Pages;

use App\Exports\PayrollDepositoExport;
use App\Filament\Resources\PayrollDepositoResource;
use App\Models\PayrollDeposito;
use App\Models\proyeksi_deposito;
use App\Models\ProyeksiDeposito;
use App\Models\RekeningTransfer;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ListPayrollDepositos extends ListRecords
{
    protected static string $resource = PayrollDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generatePayroll')
                ->label('Generate Payroll')
                ->icon('heroicon-o-calculator')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $inserted = 0;
                    $errors = 0;

                    DB::transaction(function () use (&$inserted, &$errors) {
                        PayrollDeposito::truncate();

                        $proyeksiList = proyeksi_deposito::all();

                        foreach ($proyeksiList as $item) {
                            try {
                                $rekening = RekeningTransfer::where('norek_deposito', $item->rek_deposito)->first();

                                PayrollDeposito::create([
                                    'rek_deposito'    => $item->rek_deposito,
                                    'cif'             => $item->cif,
                                    'nama_nasabah'    => $item->nama_nasabah,
                                    'jangka_waktu'    => $item->jangka_waktu,
                                    'nominal'         => $item->nominal,
                                    'total_bunga'     => $item->total_bunga,
                                    'total_pajak'     => $item->total_pajak,
                                    'total_bayar'     => $item->total_bayar,
                                    'tanggal_bayar'   => $item->tanggal_bayar,
                                    'jatuh_tempo'     => $item->jatuh_tempo,
                                    'norek_tujuan'    => $rekening->norek_tujuan ?? null,
                                    'bank_tujuan'     => $rekening->bank_tujuan ?? null,
                                    'kode_bank'       => $rekening->kode_bank ?? null,
                                    'nama_rekening'   => $rekening->nama_rekening ?? null,
                                    'emailcorporate'  => 'bprtaspen@gmail.com',
                                    'ibuobu'          => 'IBU',
                                    'currency'        => 'IDR',
                                    'remark1'         => 'Budep',
                                    'remark2'         => 'transactionRemark1',
                                    'remark3'         => 'transactionRemark2',
                                    'adjust1'         => 'valuePaymentDetails',
                                    'adjust2'         => 'N',
                                    'adjust3'         => 'N',
                                    'adjust4'         => 'extended payment detail',
                                    'adjust5'         => 'OUR',
                                    'adjust6'         => 'EPD',
                                    'adjust7'         => 'Y',
                                    'adjust8'         => '014',
                                    'adjust9'         => 'BPR0101309',
                                    'adjust10'        => '0',
                                    'adjust11'        => 'BANK MANDIRI TASPEN',
                                    'adjust12'        => '2144213178589',
                                    'adjust13'        => Carbon::now()->format('d/m/Y H.i.s'),
                                ]);

                                $inserted++;
                            } catch (\Throwable $e) {
                                $errors++;
                            }
                        }
                    });

                    Notification::make()
                        ->title('Generate Payroll Selesai')
                        ->body("Inserted: {$inserted}, Error: {$errors}")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('export1')
                ->label('Export')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->action(function () {
                    $payrolls = $this->getFilteredTableQuery();
                    //dd($payrolls);
                    $this->applySortingToTableQuery($payrolls);

                    $currentDate = new \DateTime();
                    $month = $currentDate->format('m');
                    $year = $currentDate->format('Y');

                    $tanggalBayarGrouped = proyeksi_deposito::select('tanggal_bayar')
                        ->groupBy('tanggal_bayar')
                        ->get();

                    $tanggalString = implode('_', $tanggalBayarGrouped->pluck('tanggal_bayar')->toArray());
                    $fileName = 'Rekening Tujuan Transfer Pembayaran Bunga Deposito_' . $tanggalString . '_' . $month . '_' . $year . '.xlsx';
                    log::info('Exporting to Excel', ['fileName' => $fileName]);
                    return Excel::download(new PayrollDepositoExport($payrolls), $fileName);

                }),

            // Actions\Action::make('clearData')
            //     ->label('Hapus Semua Data Payroll')
            //     ->icon('heroicon-o-trash')
            //     ->color('danger')
            //     ->requiresConfirmation()
            //     ->action(function () {
            //         PayrollDeposito::truncate();

            //         Notification::make()
            //             ->title('Data Payroll Dihapus')
            //             ->body('Seluruh data payroll deposito telah dihapus.')
            //             ->success()
            //             ->send();
            //     }),
        ];
    }
}
