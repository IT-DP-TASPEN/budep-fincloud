<?php

namespace App\Filament\Exports;

use App\Models\proyeksi_deposito;
use App\Models\ProyeksiDeposito;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class ProyeksiDepositoExporter extends Exporter
{
    protected static ?string $model = proyeksi_deposito::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('rek_deposito')->label('rek_deposito'),
            ExportColumn::make('cif')->label('CIF'),
            ExportColumn::make('nama_nasabah')->label('nama_nasabah'),

            ExportColumn::make('rekeningTransfer.norek_tujuan')->label('norek_tujuan'),
            ExportColumn::make('rekeningTransfer.nama_rekening')->label('nama_rekening'),
            ExportColumn::make('rekeningTransfer.bank_tujuan')->label('bank_tujuan'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return proyeksi_deposito::query()->whereNull('rekening_transfer_id');
    }
    public function getFileName(Export $export): string
    {
        return 'rekening_transfer_kosong_' . now()->format('Ymd') . '.xlsx';
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Export completed: ';
    }
}
