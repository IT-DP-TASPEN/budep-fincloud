<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use App\Events\UserActivityLogged;
use App\Models\PayrollDeposito;

class PayrollBIFASTBRIExport implements FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithEvents, ShouldAutoSize, WithCustomCsvSettings
{
    use Exportable, RegistersEventListeners;

    protected int $totalCount;
    protected string $norek_tujuan_string;
    protected array $empat_digit_terakhir;
    protected int $totalnominal = 0;
    protected int $tanggal_bayar;
    private $isRegistered = false;

    public function __construct(public Collection $records)
    {
        $this->tanggal_bayar = $this->records->first()->tanggal_bayar;
        $this->totalCount = $this->records->count();
        $this->norek_tujuan_string = $this->records->pluck('norek_tujuan')->implode(', ');
        $this->empat_digit_terakhir = $this->ambilEmpatDigitTerakhir($this->records->pluck('norek_deposito')->toArray());
        $this->totalnominal = $this->records->sum('total_bayar');
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    public function columnFormats(): array
    {
        return [];
    }

    public function map($payroll): array
    {
        static $index = 1;
        $day = str_pad($this->tanggal_bayar, 2, '0', STR_PAD_LEFT);
        $tanggal = 'Budep'. $day . date('m');

        return [
            $index++,
            '052701000324307',
            '00000000000',
            '',
            '',
            '',
            $payroll->norek_tujuan,
            '00',
            $payroll->kode_bank,
            $payroll->total_bayar,
            '99',
            $payroll->norek_deposito,
            $tanggal . $this->empat_digit_terakhir[$index - 2],
            '',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'RekDebit',
            'IdDebit',
            'AreaAlamatDebit',
            'StatusResidenDebit',
            'TipeDebitur',
            'RekKredit',
            'JenisInstruksiKredit',
            'KodeBank',
            'Amount',
            'KategoriTujuan',
            'InformasiPembayaran',
            'NoReferensi',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $rowCount = count($this->collection()) + 1;

                $event->sheet->setCellValue('A' . ($rowCount + 1), 'DATA');
                $event->sheet->setCellValue('B' . ($rowCount + 1), $this->totalCount);

                $event->sheet->setCellValue('A' . ($rowCount + 2), 'TOTAL');
                $event->sheet->setCellValue('B' . ($rowCount + 2), $this->totalnominal);
            },
        ];
    }

    protected function ambilEmpatDigitTerakhir(array $norek_deposito): array
    {
        return array_map(function ($rekening) {
            return empty($rekening) ? '' : substr($rekening, -5);
        }, $norek_deposito);
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getNorekTujuanString(): string
    {
        return $this->norek_tujuan_string;
    }

    public function getTotalNominal(): int
    {
        return $this->totalnominal;
    }

    public function getOptions(): array
    {
        return [
            'title' => 'Payroll Export',
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => '|',
            'enclosure' => '',
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }
}
