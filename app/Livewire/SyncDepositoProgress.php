<?php

namespace App\Http\Livewire;

use App\Models\SyncProgress;
use Livewire\Component;

class SyncDepositoProgress extends Component
{
    public $progress = 0;
    public $status = 'idle';
    public $processed = 0;
    public $total = 0;

    protected $listeners = ['refreshProgress' => '$refresh'];

    public function mount()
    {
        $this->loadProgress();
    }

    public function loadProgress()
    {
        $progress = SyncProgress::where('process_name', 'deposito_sync')->first();

        if ($progress) {
            $this->total = $progress->total;
            $this->processed = $progress->processed;
            $this->status = $progress->status;
            $this->progress = $progress->total > 0
                ? round(($progress->processed / $progress->total) * 100, 2)
                : 0;
        }
    }

    public function render()
    {
        $this->loadProgress();

        return view('livewire.sync-deposito-progress');
    }
}
