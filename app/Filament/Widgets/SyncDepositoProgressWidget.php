<?php

namespace App\Filament\Widgets;

use App\Models\SyncProgress;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Artisan;

class SyncDepositoProgressWidget extends Widget
{
    protected static string $view = 'filament.widgets.sync-deposito-progress-widget';
    protected static ?int $sort = 1;

    public $progress = 0;
    public $status = 'idle';

    public function mount()
    {
        $this->loadProgress();
    }

    public function loadProgress()
    {
        $sync = SyncProgress::where('process_name', 'deposito_sync')->latest()->first();

        if ($sync) {
            $this->progress = $sync->total > 0
                ? round(($sync->processed / $sync->total) * 100, 2)
                : 0;
            $this->status = $sync->status;
        } else {
            $this->progress = 0;
            $this->status = 'idle';
        }
    }

    public function startSync()
    {
        SyncProgress::updateOrCreate(
            ['process_name' => 'deposito_sync'],
            ['status' => 'running', 'processed' => 0]
        );

        // Jalankan artisan di background (Linux / Windows)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start /B php artisan sync:deposito', 'r'));
        } else {
            shell_exec('php artisan sync:deposito > /dev/null 2>&1 &');
        }

        $this->loadProgress();
    }


    public function pauseSync()
    {
        SyncProgress::where('process_name', 'deposito_sync')
            ->update(['status' => 'paused']);
        $this->loadProgress();
    }

    public function resumeSync()
{
    SyncProgress::where('process_name', 'deposito_sync')
        ->update(['status' => 'running']);
    $this->loadProgress();
}


    public function stopSync()
    {
        SyncProgress::where('process_name', 'deposito_sync')
            ->update(['status' => 'failed']);
        $this->loadProgress();
    }
}
