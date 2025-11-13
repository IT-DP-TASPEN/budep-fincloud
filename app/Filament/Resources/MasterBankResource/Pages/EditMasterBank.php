<?php

namespace App\Filament\Resources\MasterBankResource\Pages;

use App\Filament\Resources\MasterBankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterBank extends EditRecord
{
    protected static string $resource = MasterBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
