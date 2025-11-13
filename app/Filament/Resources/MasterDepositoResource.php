<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterDepositoResource\Pages;
use App\Models\MasterDeposito;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Notifications\Notification;

class MasterDepositoResource extends Resource
{
    protected static ?string $model = MasterDeposito::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Master Deposito';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('cif_no')
                    ->label('CIF')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('norek_deposito')
                    ->label('No. Rek Deposito')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('nama_nasabah')
                    ->label('Nama Nasabah')
                    ->searchable(),
                TextColumn::make('product_name')
                    ->label('Produk'),
                TextColumn::make('kode_cabang')
                    ->label('Cabang')
                    ->sortable(),
                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->numeric(0)
                    ->sortable(),
                TextColumn::make('bunga')
                    ->label('Bunga (%)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('tgl_mulai')
                    ->label('Tgl Mulai')
                    ->date(),
                TextColumn::make('tgl_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date(),
                TextColumn::make('aro')
                    ->label('ARO'),
                TextColumn::make(name: 'tgl_bayar')
                    ->label('Tanggal Bayar'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Closed',
                    ]),
                // 🔹 Tambahan Kolom Potong Pajak
                ToggleColumn::make('potong_pajak')
                    ->label('Potong Pajak')
                    ->onIcon('heroicon-o-check-circle')
                    ->offIcon('heroicon-o-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->getStateUsing(fn($record) => $record->potong_pajak === 'Yes')
                    ->updateStateUsing(function ($record, $state) {
                        $record->update(['potong_pajak' => $state ? 'Yes' : 'No']);

                        Notification::make()
                            ->title('Status Pajak Diperbarui')
                            ->body("Rekening deposito {$record->norek_deposito} diubah menjadi: " . ($state ? 'Yes' : 'No'))
                            ->success()
                            ->send();

                        return $state;
                    })

                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterDepositos::route('/'),
        ];
    }
}
