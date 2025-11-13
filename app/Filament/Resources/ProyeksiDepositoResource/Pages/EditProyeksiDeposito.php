<?php

namespace App\Filament\Resources\ProyeksiDepositoResource\Pages;

use App\Filament\Resources\ProyeksiDepositoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProyeksiDeposito extends EditRecord
{
    protected static string $resource = ProyeksiDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
