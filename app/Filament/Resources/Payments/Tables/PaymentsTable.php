<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentStatus;
use App\Filament\Support\MarkAsPaidAction;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->weight('bold')
                    ->placeholder('—'),
                TextColumn::make('payable_type')
                    ->label('For')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('payable.reference')
                    ->label('Payable')
                    ->placeholder('—'),
                TextColumn::make('method')
                    ->badge(),
                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('paid_at')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PaymentStatus::class),
            ])
            ->recordActions([
                MarkAsPaidAction::make(),
            ]);
    }
}
