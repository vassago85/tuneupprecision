<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes\Tables;

use App\Enums\QuoteStatus;
use App\Filament\Support\MarkAsPaidAction;
use App\Models\Quote;
use App\Support\Money;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('total_cents')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('valid_until')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('ageing')
                    ->label('Ageing')
                    ->state(function (Quote $record): string {
                        $days = $record->ageingDays();
                        if ($days === null) {
                            return '—';
                        }

                        return $days < 0 ? abs($days).'d overdue' : $days.'d left';
                    }),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(QuoteStatus::class),
            ])
            ->recordActions([
                MarkAsPaidAction::make(),
                EditAction::make(),
            ]);
    }
}
