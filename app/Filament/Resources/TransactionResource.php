<?php

namespace App\Filament\Resources;

use stdClass;
use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
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

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                ),
                Forms\Components\Select::make('warung_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('warung', 'name')
                    ->default(fn() => auth()->user()->hasRole('pemilik_warung') ? auth()->user()?->warungs()?->first()->id : null)
                    ->disabled(fn() => auth()->user()->hasRole('pemilik_warung')),
                Forms\Components\Select::make('customer_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('customer', 'name'),
                Forms\Components\Select::make('transaction_type')
                    ->options(['deposit' => 'Deposit', 'purchase' => 'Purchase'])
                    ->default('purchase')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->default('Deposit tambahan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('warung.name')
                    ->label("Warung")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.user.name')
                    ->label("Customer")
                    ->searchable(),
                Tables\Columns\TextColumn::make('transaction_type'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR'))
                    ->label("Total"),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter berdasarkan buyer
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('date')
                            ->label('Filter by Date')
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
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
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
            return $query->where('customer_id', $user->id);
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
