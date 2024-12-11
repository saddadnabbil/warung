<x-filament-widgets::widget>
    <x-filament::section>
        <div>
            <p class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Total Transaksi:</p>
            <div class=" font-bold">{{ number_format($totalTransactions, 2) }} IDR</div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
