<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('customer_id')
            ->columns([
                Tables\Columns\TextColumn::make('warung.name'),
                Tables\Columns\TextColumn::make('customer.user.name')
                    ->label('Pelanggan'),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Tipe Transaksi')
                    ->formatStateUsing(fn($state): string => $state == 'deposit' ? 'Deposit' : 'Pembelian'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn($state): string => 'Rp ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan'),
                Tables\Columns\TextColumn::make('paid')
                    ->label('Status Pembayaran')
                    ->formatStateUsing(fn($state): string => $state == true ? 'Lunas' : 'Belum Lunas'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
