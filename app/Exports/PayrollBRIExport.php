<?php

namespace App\Exports;

use App\Models\PayrollDeposito;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;

class PayrollBRIExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnFormatting,
    WithEvents,
    ShouldAutoSize,
    WithCustomCsvSettings
{
    use Exportable, RegistersEventListeners;

    protected int $totalCount;
    protected array $norek_tujuan_array;
    protected array $empat_digit_terakhir;
    protected int $totalnominal = 0;

    public function __construct(public Collection $records)
    {
        // dd($records);
        $this->totalCount = $this->records->count();
        $this->norek_tujuan_array = $this->records->pluck('norek_tujuan')->toArray();
        $this->empat_digit_terakhir = $this->ambilEmpatDigitTerakhir($this->norek_tujuan_array);
        $this->totalnominal = $this->records->sum('total_bayar');
    }

    public function collection()
    {
        return $this->records;
    }

    public function columnFormats(): array
    {
        return [
            // 'D' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function map($payroll): array
    {
        static $index = 1;

        return [
            $index++,
            $payroll->nama_nasabah,
            $payroll->norek_tujuan,
            number_format($payroll->total_bayar, 2, '', ''),
            $payroll->emailcorporate ?? 'bprtaspen@gmail.com',
        ];
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA',
            'ACCOUNT',
            'AMOUNT',
            'EMAIL',
        ];
    }

    public function registerEvents(): array
    {
        $total = $this->totalCount;
        $totalnominal = $this->totalnominal;

        $totalEmpatDigit = array_sum(
            array_map(fn($digits): int => intval($digits), $this->empat_digit_terakhir)
        );

        $md5 = md5($total . $totalnominal . $totalEmpatDigit);

        return [
            AfterSheet::class => function (AfterSheet $event) use ($total, $totalnominal, $md5) {
                $rowCount = $this->collection()->count() + 1;

                $event->sheet->setCellValue("A" . ($rowCount + 1), "COUNT");
                $event->sheet->setCellValue("D" . ($rowCount + 1), $total);

                $event->sheet->setCellValue("A" . ($rowCount + 2), "TOTAL");
                $event->sheet->setCellValue("D" . ($rowCount + 2), number_format($totalnominal, 2, '', ''));

                $event->sheet->setCellValue("A" . ($rowCount + 3), "CHECK");
                $event->sheet->setCellValue("D" . ($rowCount + 3), $md5);
            },
        ];
    }

    protected function ambilEmpatDigitTerakhir(array $norek_tujuan): array
    {
        return array_map(function ($rekening) {
            if (empty($rekening)) {
                return '';
            }

            $lastFourDigits = substr($rekening, -4);
            return $lastFourDigits[0] === '0' ? substr($lastFourDigits, 1, 3) : $lastFourDigits;
        }, $norek_tujuan);
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '',
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }
}
