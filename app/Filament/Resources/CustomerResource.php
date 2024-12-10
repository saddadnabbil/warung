<?php

namespace App\Filament\Resources;

use stdClass;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CustomerResource\RelationManagers;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Pelanggan';
    protected static ?string $modelLabel = 'Pelanggan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Customer')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->default(function ($record) {
                                if ($record !== null) {
                                    return $record->user->username;
                                }
                            })
                            ->unique(User::class, 'username'),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(User::class, 'email'),
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Select::make('warung_id')
                            ->relationship('warung', 'name', function ($query) {
                                return auth()->user()->hasRole('super_admin') ? $query :  $query->where('user_id', auth()->user()->id);
                            })
                            ->searchable()
                            ->preload()
                            ->default(auth()->user()->hasRole('super_admin') ? null : auth()->user()->warungs()->first()->id)
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state)),
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
                Tables\Columns\TextColumn::make('warung.name')
                    ->searchable()
                    ->sortable()
                    ->label('Warung'),
                Tables\Columns\TextColumn::make('user.username')
                    ->searchable()
                    ->sortable()
                    ->label('Username'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->filters([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.management");
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasRole('pemilik_warung')) {
            // Mendapatkan ID customer yang terkait dengan warung yang dimiliki pemilik
            $customerIds = $user->warungs()->with('customers')->get()->flatMap(function ($warung) {
                return $warung->customers->pluck('id');
            });

            return $query->whereIn('id', $customerIds);
        }

        // Jika super_admin, tampilkan semua customer
        return $query;
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
