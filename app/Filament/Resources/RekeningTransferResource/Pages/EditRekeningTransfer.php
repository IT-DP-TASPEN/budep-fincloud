<?php

namespace App\Filament\Resources\RekeningTransferResource\Pages;

use App\Filament\Resources\RekeningTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekeningTransfer extends EditRecord
{
    protected static string $resource = RekeningTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
