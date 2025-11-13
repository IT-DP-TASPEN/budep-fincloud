<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\MasterCif;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ImportAction;
use App\Imports\MasterCifImport;
use App\Filament\Resources\MasterCifResource\Pages;

class MasterCifResource extends Resource
{
    protected static ?string $model = MasterCif::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master CIF';
    protected static ?string $pluralModelLabel = 'Master CIF';
    protected static ?int $navigationSort = 1;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('cif')
                ->label('CIF')
                ->required()
                ->maxLength(30),

            Forms\Components\TextInput::make('alt_no')
                ->label('ALT No')
                ->maxLength(30),

            Forms\Components\TextInput::make('customer_name')
                ->label('Nama Nasabah')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('no_hp')
                ->label('No HP')
                ->tel()
                ->maxLength(20),

            Forms\Components\TextInput::make('customer_type')
                ->label('Tipe Nasabah')
                ->maxLength(50),

            Forms\Components\TextInput::make('no_ktp')
                ->label('No KTP')
                ->maxLength(30),

            Forms\Components\DatePicker::make('tgl_lahir')
                ->label('Tanggal Lahir'),

            Forms\Components\TextInput::make('kode_cabang')
                ->label('Kode Cabang')
                ->maxLength(10),

            Forms\Components\DatePicker::make('register_date')
                ->label('Tanggal Registrasi'),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'Active' => 'Active',
                    'Inactive' => 'Inactive',
                ])
                ->default('Active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cif')
                    ->label('CIF')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Nasabah')
                    ->searchable()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('customer_type')
                //     ->label('Tipe Nasabah'),

                // Tables\Columns\TextColumn::make('no_ktp')
                //     ->label('No KTP'),

                Tables\Columns\TextColumn::make('kode_cabang')
                    ->label('Kode Cabang'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'gray')
                    ->label('Status'),

                Tables\Columns\TextColumn::make('register_date')
                    ->date('Y-m-d')
                    ->label('Tgl Registrasi'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
            // ->headerActions([
            //     ImportAction::make()
            //         ->label('Import Master CIF')
            //         ->importer(MasterCifImport::class),
            // ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterCifs::route('/'),
            'create' => Pages\CreateMasterCif::route('/create'),
            'edit' => Pages\EditMasterCif::route('/{record}/edit'),
        ];
    }
}
