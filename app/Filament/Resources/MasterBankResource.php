<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterBankResource\Pages;
use App\Models\MasterBank;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MasterBankResource extends Resource
{
    protected static ?string $model = MasterBank::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Master Bank';
    protected static ?string $slug = 'master-bank';
    protected static ?int $navigationSort = 4;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Bank')
                    ->description('Isi data bank beserta kode uniknya.')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Forms\Components\TextInput::make('nama_bank')
                            ->label('Nama Bank')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: BANK MANDIRI'),

                        Forms\Components\TextInput::make('kode_bank')
                            ->label('Kode Bank (SWIFT/BIC)')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Contoh: BMRIIDJA'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nama_bank')
                    ->label('Nama Bank')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('kode_bank')
                    ->label('Kode Bank')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('nama_bank');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterBanks::route('/'),
            'create' => Pages\CreateMasterBank::route('/create'),
            'edit' => Pages\EditMasterBank::route('/{record}/edit'),
        ];
    }
}
