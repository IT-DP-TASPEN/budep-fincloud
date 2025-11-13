<?php

namespace App\Exports;

use Illuminate\Support\Facades\Hash;
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
use Illuminate\Support\Facades\Auth;
use App\Events\UserActivityLogged;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Models\PayrollDeposito;


class PayrollBNIExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithCustomCsvSettings, WithEvents
{
    use Exportable, RegistersEventListeners;

    protected int $totalCount;
    protected int $norek_tujuan;
    protected int $totalnominal = 0;
    protected int $tanggal_bayar;
    private $isRegistered = false;

    public function __construct(public Collection $records)
    {
        $this->tanggal_bayar = $this->records->first()->tanggal_bayar;
        $this->totalCount = $this->records->count();

        $this->norek_tujuan = $this->records->first()->norek_tujuan;

        $this->totalnominal = $this->records->sum('nominal');
    }

    public function collection()
    {
        return $this->records;
    }

    public function map($payroll): array
    {

        return [
            $payroll->norek_tujuan,
            $payroll->nama_rekening,
            $payroll->nominal,
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
            'N',
            '',
            '',
            'N',
        ];
    }

    public function headings(): array
    {
        $count = $this->totalCount;
        $counthead = $count + 2;
        $date = date('Y/m/d/_H.i.s');
        $day = str_pad($this->tanggal_bayar, 2, '0', STR_PAD_LEFT);
        $tanggal = 'Budep ' . date('Ym'). $day;
        //$date2 = date('Ymd');
        $totalnominal = $this->totalnominal;

        return [

            [
                $date,
                $counthead,
            ],
            [
                'P',
                $tanggal,
                '16245966',
                $count,
                $totalnominal,
            ]
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
            'delimiter' => ',', // Set the delimiter
            'enclosure' => '', // Set enclosure to empty to avoid quotes
            'use_bom' => true, // Use BOM for UTF-8
            'output_encoding' => 'UTF-8', // Set output encoding to UTF-8
        ];
    }
}
