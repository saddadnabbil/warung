<?php

namespace App\Filament\Resources;

use stdClass;
use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use Filament\Forms\Components\Grid;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Transaksi')
                    ->schema([
                        Forms\Components\Select::make('warung_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('warung', 'name')
                            ->default(fn() => auth()->user()->hasRole('pemilik_warung') ? auth()->user()?->warungs()?->first()->id : null),
                        Forms\Components\Select::make('customer_id')
                            ->options(function () {
                                return auth()->user()->hasRole('super_admin') ? Customer::with('user')->get()->pluck('user.name', 'id') : Customer::with('user')->where('warung_id', auth()->user()->warungs()->first()->id)->get()->pluck('user.name', 'id');
                            })
                            ->label('Pelanggan')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Grid::make()->schema([
                            Forms\Components\Select::make('transaction_type')
                                ->label('Tipe Transaksi')
                                ->options(['deposit' => 'Deposit', 'purchase' => 'Pembelian'])
                                ->default('purchase')
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->required()
                                ->numeric(),
                            Forms\Components\Select::make('paid')
                                ->options([true => 'Lunas', false => 'Belum Lunas'])
                                ->label('Status Pembayaran')
                                ->searchable()
                                ->default(false),
                        ])->columns(3),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->default('Deposit tambahan'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                Tables\Columns\TextColumn::make('warung.name')
                    ->label("Warung")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.user.name')
                    ->label("Pelanggan")
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->label("Tipe Transaksi")
                    ->sortable()
                    ->formatStateUsing(fn($state): string => $state == 'deposit' ? 'Deposit' : 'Pembelian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paid')
                    ->label("Status Pembayaran")
                    ->formatStateUsing(fn($state): string => $state == true ? 'Lunas' : 'Belum Lunas')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(Transaction $record): ?string => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR'))
                    ->label("Jumlah"),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->visible(fn() => auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('pemilik_warung'))
                    ->label('Pelanggan')
                    ->options(function () {
                        return auth()->user()->hasRole('super_admin') ? Customer::with('user')->get()->pluck('user.name', 'id') : Customer::with('user')->where('warung_id', auth()->user()->warungs()->first()->id)->get()->pluck('user.name', 'id');
                    })
                    ->searchable(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('date')
                            ->label('Tanggal')
                    ])
                    ->default(Carbon::now()->format('Y-m-d'))
                    ->query(function (Builder $query, array $data) {
                        if ($data['date']) {
                            $query->whereDate('created_at', '=', $data['date']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['date']) {
                            return null;
                        }

                        return 'Created at ' . Carbon::parse($data['date'])->toFormattedDateString();
                    }),
                SelectFilter::make('transaction_type')
                    ->label("Tipe Transaksi")
                    ->options(['deposit' => 'Deposit', 'purchase' => 'Pembelian'])
                    ->searchable()
                    ->default('purchase'),
                SelectFilter::make('paid')
                    ->label("Status Pembayaran")
                    ->options([true => 'Lunas', false => 'Belum Lunas'])
                    ->searchable()
                    ->default(true),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(function () {
                if (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('pemilik_warung')) {
                    return 4;
                } else {
                    return 3;
                }
            })
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasRole('pembeli')) {
            return $query->where('customer_id', $user->customer()->first()->id);
        } elseif ($user->hasRole('pemilik_warung')) {
            $warungIds = $user->warungs()->pluck('id');
            return $query->whereIn('warung_id', $warungIds);
        }

        return $query;
    }


    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.management");
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
