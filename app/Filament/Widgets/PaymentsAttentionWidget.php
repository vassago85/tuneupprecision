<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PaymentsAttentionWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Payments needing attention';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->with('payable')
                    ->where('status', PaymentStatus::Pending)
                    ->where('method', PaymentMethod::Eft)
                    ->latest()
            )
            ->columns([
                TextColumn::make('reference')
                    ->label('Ref')
                    ->weight('semibold')
                    ->copyable(),
                TextColumn::make('payable.reference')
                    ->label('For')
                    ->color('gray'),
                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->money('ZAR', divideBy: 100)
                    ->alignEnd(),
            ])
            ->emptyStateHeading('All caught up')
            ->emptyStateDescription('No EFT payments awaiting confirmation.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
