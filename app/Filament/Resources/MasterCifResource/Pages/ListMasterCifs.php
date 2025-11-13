<?php

namespace App\Filament\Resources\MasterCifResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Imports\MasterCifImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Filament\Resources\MasterCifResource;
use App\Models\MasterCif;

class ListMasterCifs extends ListRecords
{
    protected static string $resource = MasterCifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // 🔽 Tombol Import CSV
            Action::make('import_CIF')
                ->label('Import Master CIF')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('File CSV')
                        ->required()
                        ->disk('public')
                        ->directory('imports')
                        ->acceptedFileTypes(['text/csv'])
                        ->visibility('public'),
                ])
                ->action(function (array $data, Action $action) {
                    try {
                        $path = storage_path('app/public/' . $data['file']);
                        Log::info('📂 Path file import: ' . $path);

                        Excel::import(new MasterCifImport, $path);

                        Notification::make()
                            ->title('Import Berhasil ✅')
                            ->body('Data Master CIF berhasil diimpor.')
                            ->success()
                            ->send();
                    } catch (\Throwable $th) {
                        Log::error('❌ Gagal import Master CIF', [
                            'error' => $th->getMessage(),
                            'trace' => $th->getTraceAsString(),
                        ]);

                        Notification::make()
                            ->title('Gagal Import ❌')
                            ->body('Terjadi kesalahan saat mengimpor data: ' . $th->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Action::make('truncate_CIF')
            //     ->label('Hapus Semua Data')
            //     ->requiresConfirmation()
            //     ->action(function () {
            //         try {
            //             DB::beginTransaction();

            //             DB::statement('SET FOREIGN_KEY_CHECKS=0');

            //             DB::statement('TRUNCATE TABLE deposits');
            //             DB::statement('TRUNCATE TABLE master_cifs');

            //             DB::statement('SET FOREIGN_KEY_CHECKS=1');

            //             DB::commit();

            //             Notification::make()
            //                 ->title('Data Dihapus 🗑️')
            //                 ->success()
            //                 ->send();
            //         } catch (\Throwable $th) {
            //             DB::rollBack();
            //             Log::error('❌ Gagal truncate Master CIF & Deposits: ' . $th->getMessage());
            //             Notification::make()
            //                 ->title('Gagal Menghapus ❌')
            //                 ->body($th->getMessage())
            //                 ->danger()
            //                 ->send();
            //         }
            //     }),
        ];
    }
}
