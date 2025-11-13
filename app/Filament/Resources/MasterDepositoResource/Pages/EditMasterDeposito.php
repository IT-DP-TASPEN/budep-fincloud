<?php

namespace App\Filament\Resources\MasterDepositoResource\Pages;

use App\Filament\Resources\MasterDepositoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterDeposito extends EditRecord
{
    protected static string $resource = MasterDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
