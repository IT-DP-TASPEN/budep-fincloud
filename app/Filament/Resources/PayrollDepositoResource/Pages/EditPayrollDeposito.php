<?php

namespace App\Filament\Resources\PayrollDepositoResource\Pages;

use App\Filament\Resources\PayrollDepositoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollDeposito extends EditRecord
{
    protected static string $resource = PayrollDepositoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
