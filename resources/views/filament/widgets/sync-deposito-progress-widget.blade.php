<x-filament::section>
    <div wire:key="sync-progress-widget" wire:poll.3s="loadProgress" class="space-y-4">

        <div class="text-base font-semibold">🔁 Sinkronisasi Deposito</div>

        {{-- Progress Bar --}}
        <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
            <div
                class="h-3 rounded-full transition-all duration-700 ease-in-out
                    @if($status === 'running') bg-green-500
                    @elseif($status === 'paused') bg-yellow-500
                    @elseif($status === 'failed') bg-red-500
                    @elseif($status === 'completed') bg-blue-500
                    @else bg-gray-400 @endif"
                style="width: {{ $progress }}%;"
            ></div>
        </div>

        <div class="flex justify-between text-sm">
            <span>Status: <strong class="uppercase">{{ $status }}</strong></span>
            <span>{{ $progress }}%</span>
        </div>

        <div class="flex gap-2">
            @if ($status === 'idle' || $status === 'completed' || $status === 'failed')
                <x-filament::button color="success" wire:click="startSync">▶ Mulai</x-filament::button>
            @elseif ($status === 'running')
                <x-filament::button color="warning" wire:click="pauseSync">⏸ Jeda</x-filament::button>
                <x-filament::button color="danger" wire:click="stopSync">⏹ Hentikan</x-filament::button>
            @elseif ($status === 'paused')
                <x-filament::button color="success" wire:click="startSync">▶ Lanjutkan</x-filament::button>
                <x-filament::button color="danger" wire:click="stopSync">⏹ Stop</x-filament::button>
            @endif
        </div>
    </div>
</x-filament::section>
