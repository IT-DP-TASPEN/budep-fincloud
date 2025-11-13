<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProyeksiDepositoExporter;
use App\Filament\Resources\ProyeksiDepositoResource\Pages;
use App\Models\proyeksi_deposito;
use App\Models\ProyeksiDeposito;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ExportBulkAction;

class ProyeksiDepositoResource extends Resource
{
    protected static ?string $model = proyeksi_deposito::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Proyeksi Deposito';
    protected static ?string $navigationGroup = 'Transaksi Deposito';
    protected static ?string $slug = 'proyeksi-bunga-deposito';
    protected static ?int $navigationSort = 1;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('rek_deposito')
                    ->label('No. Rek Deposito')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('cif')
                    ->label('CIF')
                    ->copyable()
                    ->sortable(),

                TextColumn::make('nama_nasabah')
                    ->label('Nama Nasabah')
                    ->searchable(),

                TextColumn::make('jangka_waktu')
                    ->label('Jangka Waktu (Hari)')
                    ->sortable(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_bunga')
                    ->label('Total Bunga')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('total_pajak')
                    ->label('Total Pajak')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('total_bayar')
                    ->label('Total Bayar')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('tanggal_bayar')
                    ->label('Tanggal Bayar (Hari)')
                    ->sortable(),

                TextColumn::make('jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date()
                    ->sortable(),

                TextColumn::make('rekeningTransfer.norek_tujuan')
                    ->label('Rek. Tujuan')
                    ->default('-'),

                TextColumn::make('rekeningTransfer.nama_rekening')
                    ->label('Nama Rekening Tujuan')
                    ->default('-'),

                TextColumn::make('rekeningTransfer.bank_tujuan')
                    ->label('Bank Tujuan')
                    ->default('-'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\Filter::make('rekening_kosong')
                    ->label('Tanpa Rekening Transfer')
                    ->query(fn($query) => $query->whereNull('rekening_transfer_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make('export')
                    ->label('Export Rekening Kosong')
                    ->exporter(ProyeksiDepositoExporter::class)
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray'),

                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProyeksiDepositos::route('/'),
        ];
    }
}
