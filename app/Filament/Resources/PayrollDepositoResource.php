<?php

namespace App\Filament\Resources;

use App\Models\PayrollDeposito;
use App\Exports\PayrollMandiriExport;
use App\Exports\PayrollBNIExport;
use App\Exports\PayrollBRIExport;
use App\Exports\PayrollBIFASTBRIExport;
use App\Filament\Resources\PayrollDepositoResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class PayrollDepositoResource extends Resource
{
    protected static ?string $model = PayrollDeposito::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Payroll Deposito';
    protected static ?string $navigationGroup = 'Transaksi Deposito';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('rek_deposito')->label('No. Rek Deposito')->required(),
            Forms\Components\TextInput::make('nama_nasabah')->label('Nama Nasabah')->required(),
            Forms\Components\TextInput::make('norek_tujuan')->label('No. Rek Tujuan')->required(),
            Forms\Components\TextInput::make('bank_tujuan')->label('Bank Tujuan')->required(),
            Forms\Components\TextInput::make('kode_bank')->label('Kode Bank'),
            Forms\Components\TextInput::make('nama_rekening')->label('Nama Rekening'),
            Forms\Components\TextInput::make('nominal')->numeric()->label('Nominal'),
            Forms\Components\TextInput::make('total_bunga')->numeric()->label('Total Bunga'),
            Forms\Components\DatePicker::make('tanggal_bayar')->label('Tanggal Bayar'),
            Forms\Components\DatePicker::make('jatuh_tempo')->label('Jatuh Tempo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Payroll Deposito')
            ->striped()
            ->columns([
                TextColumn::make('rek_deposito')->label('No. Rek Deposito')->sortable()->searchable()->copyable(),
                TextColumn::make('nama_nasabah')->label('Nama Nasabah')->sortable()->searchable(),
                TextColumn::make('norek_tujuan')->label('No. Rek Tujuan')->sortable()->copyable(),
                TextColumn::make('bank_tujuan')->label('Bank Tujuan')->sortable(),
                TextColumn::make('kode_bank')->label('Kode Bank'),
                TextColumn::make('nama_rekening')->label('Nama Rekening'),
                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->alignment(Alignment::Right)
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->summarize(Sum::make()->label('Total Nominal')->money('IDR')),
                TextColumn::make('total_bunga')
                    ->label('Total Bunga')
                    ->alignment(Alignment::Right)
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->summarize(Sum::make()->label('Total Bunga')->money('IDR')),
                TextColumn::make('tanggal_bayar')->label('Tanggal Bayar'),
                TextColumn::make('jatuh_tempo')->label('Jatuh Tempo')->date(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('bank_tujuan')
                    ->label('Bank Tujuan')
                    ->multiple()
                    ->searchable()
                    ->options(
                        PayrollDeposito::distinct()
                            ->pluck('bank_tujuan', 'bank_tujuan')
                            ->filter(fn($value) => !is_null($value))
                    ),

                Filter::make('bank_tujuan2')
                    ->label('BI Fast')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereNotIn('bank_tujuan', ['BRI', 'MANDIRI'])
                    ),

                SelectFilter::make('tanggal_bayar')
                    ->label('Tanggal Bayar')
                    ->multiple()
                    ->searchable()
                    ->options(
                        PayrollDeposito::distinct()
                            ->pluck('tanggal_bayar', 'tanggal_bayar')
                            ->filter(fn($value) => !is_null($value))
                    ),

            ], layout: FiltersLayout::AboveContent)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_mandiri')
                        ->label('Mandiri')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Collection $records) {

                            $tanggal_bayar = $records->pluck('tanggal_bayar')->first();

                            $bulan = date('m');
                            $tahun = date('Y');

                            $tanggal = $tanggal_bayar . '-' . $bulan . '-' . $tahun;

                            $fileName = 'Budep_Mandiri_' . $tanggal . '.csv';

                            return Excel::download(new PayrollMandiriExport($records), $fileName);
                        }),

                    Tables\Actions\BulkAction::make('export_bni')
                        ->label('BNI')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Collection $records) {
                            $tanggal_bayar = $records->pluck('tanggal_bayar')->first();

                            $bulan = date('m');
                            $tahun = date('Y');

                            $tanggal = $tanggal_bayar . '-' . $bulan . '-' . $tahun;
                            $fileName = 'Budep_BNI_' . $tanggal . '.csv';

                            return Excel::download(new PayrollBNIExport($records), $fileName);
                        }),

                    Tables\Actions\BulkAction::make('export_bri')
                        ->label('BRI')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Collection $records) {
                            $tanggal_bayar = $records->pluck('tanggal_bayar')->first();

                            $bulan = date('m');
                            $tahun = date('Y');

                            $tanggal = $tanggal_bayar . '-' . $bulan . '-' . $tahun;
                            $fileName = 'Budep_BRI_' . $tanggal . '.csv';
                            return Excel::download(new PayrollBRIExport($records), $fileName);
                        }),

                    Tables\Actions\BulkAction::make('export_bifast_bri')
                        ->label('BI-FAST BRI')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Collection $records) {
                            $tanggal_bayar = $records->pluck('tanggal_bayar')->first();

                            $bulan = date('m');
                            $tahun = date('Y');

                            $tanggal = $tanggal_bayar . '_' . $bulan . '_' . $tahun;
                            $fileName = 'Budep_BIFast BRI_' . $tanggal . '.csv';
                            return Excel::download(new PayrollBIFASTBRIExport($records), $fileName);
                        }),
                ])->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray'),

                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrollDepositos::route('/'),
            'create' => Pages\CreatePayrollDeposito::route('/create'),
            'edit' => Pages\EditPayrollDeposito::route('/{record}/edit'),
        ];
    }
}
