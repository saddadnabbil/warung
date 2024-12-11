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
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Tanggal Transaksi')
                            ->default(Carbon::now())
                            ->native(false)
                            ->required(),
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
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->default('Deposit tambahan'),
                    ])->columns(3),
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
                    ->label("Tanggal Transaksi")
                    ->dateTime()
                    ->sortable(),
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
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('created_from')
                                    ->label('Dari Tanggal')
                                    ->default(Carbon::now()->startOfMonth())
                                    ->native(false),
                                Forms\Components\DatePicker::make('created_until')
                                    ->label('Sampai Tanggal')
                                    ->default(Carbon::now())
                                    ->native(false),
                            ])
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['created_from']) && !empty($data['created_until'])) {
                            $query->whereBetween('created_at', [
                                Carbon::parse($data['created_from'])->startOfDay(),
                                Carbon::parse($data['created_until'])->endOfDay(),
                            ]);
                        } elseif (!empty($data['created_from'])) {
                            $query->whereDate('created_at', '>=', Carbon::parse($data['created_from'])->startOfDay());
                        } elseif (!empty($data['created_until'])) {
                            $query->whereDate('created_at', '<=', Carbon::parse($data['created_until'])->endOfDay());
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!empty($data['created_from']) && !empty($data['created_until'])) {
                            return 'From ' . Carbon::parse($data['created_from'])->toFormattedDateString() . ' to ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        } elseif (!empty($data['created_from'])) {
                            return 'From ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        } elseif (!empty($data['created_until'])) {
                            return 'Until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return null;
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
