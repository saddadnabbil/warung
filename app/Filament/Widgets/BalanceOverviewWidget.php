<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
use App\Models\Transaction;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class BalanceOverviewWidget extends Widget
{
    protected static ?int $sort = 1; // Urutan widget di dashboard

    protected static string $view = 'filament.widgets.balance-overview-widget';

    public $totalBalance;
    public $totalTransactions;

    public function mount()
    {
        // Mengambil total balance dari semua user
        $this->totalBalance = Balance::sum('balance');

        // Mengambil total jumlah transaksi
        $this->totalTransactions = Transaction::count();
    }
}
