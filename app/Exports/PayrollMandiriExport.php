<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\Auth;
use App\Events\UserActivityLogged;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\PayrollDeposito;


class PayrollMandiriExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithCustomCsvSettings, WithEvents
{
    use Exportable, RegistersEventListeners;

    protected int $totalCount;
    protected int $norek_tujuan;
    protected int $tanggal_bayar;
    protected int $totalnominal = 0;
    private $isRegistered = false;


    public function __construct(public collection $records)
    {

        //dd($records);
        $this->totalCount = $records->count();
        $this->norek_tujuan = $records->first()->norek_tujuan;
        $this->totalnominal = $records->sum('total_bayar');
        $this->tanggal_bayar = $records->first()->tanggal_bayar;
    }

    public function collection()
    {
        return $this->records;
    }

    public function map($records): array
    {
        //dd($records);
        static $index = 1;
        static $angka = "008";
        $day = $records->tanggal_bayar;
        $tanggal = 'Budep '. $day . date(' M y');
        //dd($esokHari);

        return [
            $records->norek_tujuan,
            $records->nama_nasabah,
            '',
            '',
            '',
            'IDR',
            $records->total_bayar,
            $tanggal,
            $records->norek_deposito,
            'IBU',
            $angka,
            '',
            '',
            '',
            '',
            '',
            'N',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'OUR',
            '',
            'EPD' . $index++,
        ];
    }

    public function headings(): array
    {
        $day = str_pad($this->tanggal_bayar, 2, '0', STR_PAD_LEFT);
        $tanggal = date('Ym'). $day;
        $total = $this->totalCount;
        $totalnominal = $this->totalnominal;

        return [

            'P',
            $tanggal,
            '1670018119908',
            $total,
            $totalnominal,
        ];
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function norek_tujuan(): int
    {
        return $this->norek_tujuan;
    }

    public function getTotalnominal(): int
    {
        return $this->totalnominal;
    }

    public function getOptions(): array
    {
        return [
            'title' => 'payrolls Export',
        ];
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
