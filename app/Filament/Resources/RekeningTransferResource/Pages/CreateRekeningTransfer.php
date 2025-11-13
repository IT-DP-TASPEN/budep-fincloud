<?php

namespace App\Filament\Resources\RekeningTransferResource\Pages;

use App\Filament\Resources\RekeningTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRekeningTransfer extends CreateRecord
{
    protected static string $resource = RekeningTransferResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
            $data['user_id'] = auth()->id();
        \App\Models\MasterCif::firstOrCreate(
            ['cif' => $data['cif']],
            [
                'customer_name' => $data['customer_name'] ?? '',
                'no_hp' => $data['no_hp'] ?? '',
                'no_ktp' => $data['no_ktp'] ?? '',
                'kode_cabang' => $data['kode_cabang'] ?? '',
                'user_id' => $data['user_id'],
                'status' => 'active',
                'register_date' => now(),
            ]
        );

        return $data;
    }
}
