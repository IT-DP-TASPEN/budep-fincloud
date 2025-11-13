<?php

namespace App\Filament\Resources\MasterCifResource\Pages;

use App\Filament\Resources\MasterCifResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterCif extends EditRecord
{
    protected static string $resource = MasterCifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
