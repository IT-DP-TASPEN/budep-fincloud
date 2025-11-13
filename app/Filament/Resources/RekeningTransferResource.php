<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekeningTransferResource\Pages;
use App\Models\RekeningTransfer;
use App\Models\MasterCif;
use App\Models\MasterBank;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RekeningTransferResource extends Resource
{
    protected static ?string $model = RekeningTransfer::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Master Rekening Transfer';
    protected static ?string $slug = 'rekening-transfer-bunga';
    protected static ?int $navigationSort = 3;

    /** 🔹 Daftar Cabang */
    public static function getCabangOptions(): array
    {
        return [
            '001' => '001 - KPO',
            '002' => '002 - KC Bogor',
            '003' => '003 - KC Depok',
            '004' => '004 - KC Tangerang',
            '005' => '005 - KC Jaktim',
            '006' => '006 - KC Karawang',
            '007' => '007 - KC Cikarang',
            '008' => '008 - KC Purwokerto',
        ];
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        $cabangOptions = self::getCabangOptions();

        return $form->schema([
            Forms\Components\Section::make('Data CIF Nasabah')
                ->description('Masukkan data dasar nasabah baru atau CIF yang sudah ada.')
                ->icon('heroicon-o-user')
                ->schema([
                    Forms\Components\TextInput::make('cif')
                        ->label('Nomor CIF')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $cif = MasterCif::where('cif', $state)->first();
                            if ($cif) {
                                $set('customer_name', $cif->customer_name);
                                $set('no_hp', $cif->no_hp);
                                $set('no_ktp', $cif->no_ktp);
                                $set('kode_cabang', $cif->kode_cabang);
                            } else {
                                $set('customer_name', null);
                                $set('no_hp', null);
                                $set('no_ktp', null);
                                $set('kode_cabang', null);
                            }
                        })
                        ->afterStateHydrated(function ($set, $record) {
                            if ($record && $record->masterCif) {
                                $set('customer_name', $record->masterCif->customer_name);
                                $set('no_hp', $record->masterCif->no_hp);
                                $set('no_ktp', $record->masterCif->no_ktp);
                                $set('kode_cabang', $record->masterCif->kode_cabang);
                            }
                        }),

                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nama Nasabah')
                        ->required(),

                    Forms\Components\TextInput::make('no_hp')
                        ->label('No. Handphone')
                        ->tel(),

                    Forms\Components\TextInput::make('no_ktp')
                        ->label('No. KTP')
                        ->numeric(),

                    Forms\Components\Select::make('kode_cabang')
                        ->label('Kode Cabang')
                        ->options($cabangOptions)
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Data Rekening Deposito')
                ->icon('heroicon-o-banknotes')
                ->description('Isi rekening deposito & tujuan transfer bunga.')
                ->schema([
                    Forms\Components\TextInput::make('norek_deposito')
                        ->label('No. Rekening Deposito')
                        ->required(),

                    Forms\Components\TextInput::make('norek_tujuan')
                        ->label('No. Rekening Tujuan')
                        ->required(),

                    Forms\Components\Select::make('bank_tujuan')
                        ->label('Bank Tujuan')
                        ->options(fn() => MasterBank::pluck('nama_bank', 'nama_bank'))
                        ->searchable()
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(fn($state, callable $set) =>
                            $set('kode_bank', MasterBank::where('nama_bank', $state)->value('kode_bank'))
                        ),

                    Forms\Components\TextInput::make('kode_bank')
                        ->label('Kode Bank')
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Forms\Components\TextInput::make('nama_rekening')
                        ->label('Nama Pemilik Rekening')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    /** 🔹 Simpan user_id & update Master CIF */
    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = Auth::id();

        MasterCif::updateOrCreate(
            ['cif' => $data['cif']],
            [
                'customer_name' => $data['customer_name'],
                'no_hp'         => $data['no_hp'],
                'no_ktp'        => $data['no_ktp'],
                'kode_cabang'   => $data['kode_cabang'],
                'status'        => 'active',
            ]
        );

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cif')->label('CIF')->sortable()->copyable()->searchable(),
                // Tables\Columns\TextColumn::make('masterCif.customer_name')->label('Nama Nasabah')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('norek_deposito')->label('No. Rek Deposito')->copyable(),
                Tables\Columns\TextColumn::make('norek_tujuan')->label('No. Rek Tujuan')->copyable(),
                Tables\Columns\TextColumn::make('bank_tujuan')->label('Bank Tujuan'),
                Tables\Columns\TextColumn::make('kode_bank')->label('Kode Bank'),
                Tables\Columns\TextColumn::make('nama_rekening')->label('Nama Rekening'),
                Tables\Columns\TextColumn::make('user.name')->label('Dibuat Oleh')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Diperbarui')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekeningTransfers::route('/'),
            'create' => Pages\CreateRekeningTransfer::route('/create'),
            'edit' => Pages\EditRekeningTransfer::route('/{record}/edit'),
        ];
    }
}
