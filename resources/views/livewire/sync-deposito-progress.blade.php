<div wire:poll.2s class="p-4">
    @if ($status === 'running')
        <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
            <div
                class="bg-green-500 h-4 rounded-full transition-all duration-500"
                style="width: {{ $progress }}%">
            </div>
        </div>
        <div class="text-sm text-gray-700">
            <strong>{{ $processed }}</strong> / {{ $total }} data diproses ({{ $progress }}%)
        </div>
    @elseif ($status === 'completed')
        <div class="w-full bg-green-100 rounded-full h-4 mb-2">
            <div class="bg-green-600 h-4 rounded-full" style="width: 100%"></div>
        </div>
        <div class="text-sm text-green-700 font-semibold">
            ✅ Sinkronisasi selesai!
        </div>
    @else
        <div class="text-sm text-gray-500 italic">Belum ada proses sinkronisasi berjalan.</div>
    @endif
</div>
