<?php

namespace App\Filament\Resources;

use stdClass;
use Filament\Forms;
use Filament\Tables;
use App\Models\Balance;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BalanceResource\Pages;
use Filament\Forms\Components\Section;

class BalanceResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Deposit';
    protected static ?string $modelLabel = 'Deposit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Balance')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->options(function () {
                                return auth()->user()->hasRole('super_admin') ? Customer::with('user')->get()->pluck('user.name', 'id') : Customer::with('user')->where('warung_id', auth()->user()->warungs()->first()->id)->get()->pluck('user.name', 'id');
                            })
                            ->unique('balances', 'customer_id')
                            ->label('Pelanggan')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('balance')
                            ->required()
                            ->prefix('Rp '),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // iterations
                TextColumn::make('index')->getStateUsing(
                    static function (stdClass $rowLoop, HasTable $livewire): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->tableRecordsPerPage * (
                                $livewire->getPage() - 1
                            ))
                        );
                    }
                )
                    ->label('#'),
                Tables\Columns\TextColumn::make('customer.user.name')
                    ->label('Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasRole('pemilik_warung')) {
            $customerIds = $user->warungs()->with('customers')->get()->flatMap(function ($warung) {
                return $warung->customers->pluck('id');
            });

            return $query->whereIn('customer_id', $customerIds);
        } elseif ($user->hasRole('pembeli')) {
            return $query->where('customer_id', $user->customer()->first()->id);
        }

        // Jika super_admin, tampilkan semua balances
        return $query;
    }

    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.management");
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBalances::route('/'),
            'create' => Pages\CreateBalance::route('/create'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
        ];
    }
}
