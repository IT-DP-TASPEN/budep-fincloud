<?php

namespace App\Filament\Resources\MasterBankResource\Pages;

use App\Filament\Resources\MasterBankResource;
use App\Models\MasterBank;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ListMasterBanks extends ListRecords
{
    protected static string $resource = MasterBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 🔹 Import Excel
            Action::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Import Data Master Bank')
                ->modalDescription('Unggah file Excel (.xlsx) yang berisi daftar bank.')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('Pilih File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    try {
                        /** @var UploadedFile $file */
                        $file = $data['file'];
                        $path = $file->getRealPath();

                        $rows = Excel::toCollection(null, $path)->first();

                        foreach ($rows as $row) {
                            if (!isset($row[0]) || !isset($row[1])) {
                                continue;
                            }

                            MasterBank::updateOrCreate(
                                ['nama_bank' => trim($row[0])],
                                ['kode_bank' => trim($row[1])]
                            );
                        }

                        Notification::make()
                            ->title('Import Berhasil ✅')
                            ->body('Data bank berhasil diimpor ke sistem.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Import ❌')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // 🔹 Tombol Kosongkan Tabel
            // Action::make('truncateTable')
            //     ->label('Kosongkan Tabel')
            //     ->icon('heroicon-o-trash')
            //     ->color('danger')
            //     ->requiresConfirmation()
            //     ->modalHeading('Yakin ingin mengosongkan tabel Master Bank?')
            //     ->modalDescription('Seluruh data akan dihapus secara permanen dari database.')
            //     ->modalSubmitActionLabel('Ya, Hapus Semua')
            //     ->action(function () {
            //         try {
            //             DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            //             MasterBank::truncate();
            //             DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            //             Notification::make()
            //                 ->title('Tabel Dikosongkan ✅')
            //                 ->body('Seluruh data Master Bank berhasil dihapus.')
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

            // 🔹 Create Action
            Actions\CreateAction::make(),
        ];
    }
}
