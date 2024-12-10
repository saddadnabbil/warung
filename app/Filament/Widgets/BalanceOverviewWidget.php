<?php

namespace App\Filament\Widgets;

use App\Models\Balance;
use App\Models\Transaction;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class BalanceOverviewWidget extends Widget
{
    protected static ?int $sort = 1; // Urutan widget di dashboard

    protected static string $view = 'filament.widgets.balance-overview-widget';

    public $totalBalance;
    public $totalTransactions;

    public function mount()
    {
        if (auth()->user()->hasRole('super_admin')) {
            $this->totalBalance = Balance::sum('balance');

            $this->totalTransactions = Transaction::count();
        } elseif (auth()->user()->hasRole('pemilik_warung')) {
            $this->totalBalance = Balance::whereHas('customer', function (Builder $query) {
                $query->where('warung_id', auth()->user()->warungs()->first()->id);
            })->sum('balance');

            $this->totalTransactions = Transaction::whereHas('customer', function (Builder $query) {
                $query->where('warung_id', auth()->user()->warungs()->first()->id);
            })->where('transaction_type', 'purchase')->where('amount', '>', 0)->sum('amount');;
        }
    }
}
