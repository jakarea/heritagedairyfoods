<x-filament-widgets::widget>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-filament::card>
            <h4>Pending Orders</h4>
            <p class="text-xl font-semibold">{{ $pendingCount }}</p>
        </x-filament::card>
    
        <x-filament::card>
            <h4>Processing Orders</h4>
            <p class="text-xl font-semibold">{{ $processingCount }}</p>
        </x-filament::card>
    
        <x-filament::card>
            <h4>Shipped Orders</h4>
            <p class="text-xl font-semibold">{{ $shippedCount }}</p>
        </x-filament::card>
    
        <x-filament::card>
            <h4>Completed Orders</h4>
            <p class="text-xl font-semibold">{{ $completedCount }}</p>
        </x-filament::card>
    
        <x-filament::card>
            <h4>Canceled Orders</h4>
            <p class="text-xl font-semibold">{{ $canceledCount }}</p>
        </x-filament::card>
    </div>
</x-filament-widgets::widget>
