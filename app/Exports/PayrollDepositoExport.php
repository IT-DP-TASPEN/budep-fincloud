<?php

namespace App\Exports;

use App\Models\PayrollDeposito;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class PayrollDepositoExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query ?: PayrollDeposito::query();
    }

    public function collection()
    {
         $data = $this->query->get();
        return $data;
    }


    public function map($item): array
    {
        static $i = 1;

        $tfVia = match ($item->bank_tujuan) {
            'BRI' => 'BRI',
            'MANDIRI' => 'MANDIRI',
            default => 'BI-FAST',
        };

        $today = Carbon::now();
        $lastDay = $today->copy()->endOfMonth();

        $tanggalBayar = $item->tanggal_bayar ?: $lastDay->day;

        return [
            $i++,
            $item->nama_nasabah,
            $item->rek_deposito,
            "'" . $item->norek_tujuan,
            $item->bank_tujuan,
            number_format($item->nominal, 2, '.', ''),
            $tfVia,
            $tanggalBayar,
            $item->nama_rekening,
        ];
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA NASABAH',
            'NOREK DEPOSITO',
            'NOREK TUJUAN',
            'BANK TUJUAN',
            'NOMINAL',
            'TF VIA',
            'TANGGAL BAYAR',
            'NAMA PENERIMA',
        ];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $queryBuilder = clone $this->query;
                $queryBuilder->getQuery()->orders = null;

                $totals = $queryBuilder
                    ->select(
                        'tanggal_bayar',
                        DB::raw("
                            CASE
                                WHEN bank_tujuan = 'BRI' THEN 'BRI'
                                WHEN bank_tujuan = 'MANDIRI' THEN 'MANDIRI'
                                ELSE 'BI-FAST'
                            END AS tf_via
                        "),
                        DB::raw('SUM(nominal) AS total_nominal')
                    )
                    ->groupBy('tanggal_bayar', 'tf_via')
                    // ->orderBy('id', 'desc')
                    ->orderBy('tanggal_bayar', 'asc')
                    ->get();

                $rowStart = $this->collection()->count() + 3;
                $sheet = $event->sheet;

                $sheet->setCellValue("A{$rowStart}", 'Tanggal');
                $sheet->setCellValue("B{$rowStart}", 'TF VIA');
                $sheet->setCellValue("C{$rowStart}", 'TOTAL NOMINAL');

                $row = $rowStart + 1;

                foreach ($totals as $total) {
                    $sheet->setCellValue("A{$row}", $total->tanggal_bayar);
                    $sheet->setCellValue("B{$row}", $total->tf_via);
                    $sheet->setCellValue("C{$row}", number_format($total->total_nominal, 2, '.', ''));
                    $row++;
                }
            },
        ];
    }
}
