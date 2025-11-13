<?php

namespace App\Filament\Resources\RekeningTransferResource\Pages;

use Filament\Forms;
use Filament\Actions;
use Filament\Actions\Action;
use App\Models\RekeningTransfer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RekeningTransferImport;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\RekeningTransferResource;

class ListRekeningTransfers extends ListRecords
{
    protected static string $resource = RekeningTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importExcel')
                ->label('Import dari Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Pilih File Excel')
                        ->required()
                        ->disk('public')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->visibility('public'),
                ])
                ->action(function (array $data) {
                    try {
                        $path = storage_path('app/public/' . $data['file']);

                        if (!file_exists($path)) {
                            throw new \Exception("File tidak ditemukan di path: {$path}");
                        }

                        Excel::import(new RekeningTransferImport, $path);

                        Notification::make()
                            ->title('Import Berhasil ✅')
                            ->body('Data rekening transfer berhasil diimpor ke database.')
                            ->success()
                            ->send();
                    } catch (\Throwable $th) {
                        Notification::make()
                            ->title('Import Gagal ❌')
                            ->body('Terjadi kesalahan: ' . $th->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // 🔹 Tombol Truncate Table
            // Action::make('truncateTable')
            //     ->label('Kosongkan Tabel')
            //     ->icon('heroicon-o-trash')
            //     ->color('danger')
            //     ->requiresConfirmation()
            //     ->modalHeading('Yakin ingin mengosongkan tabel Rekening Transfer?')
            //     ->modalDescription('Semua data akan dihapus secara permanen.')
            //     ->modalSubmitActionLabel('Ya, Hapus Semua')
            //     ->action(function () {
            //         try {
            //             DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            //             RekeningTransfer::truncate();
            //             DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            //             Notification::make()
            //                 ->title('Berhasil ✅')
            //                 ->body('Seluruh data Rekening Transfer telah dihapus.')
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

            // 🔹 Tombol Create Manual
            Actions\CreateAction::make(),
        ];
    }
}
