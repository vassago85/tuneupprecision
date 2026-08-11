<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\TrainingEvent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingTrainingWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming training';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TrainingEvent::query()
                    ->with(['courseTemplate.trainingType', 'trainingType'])
                    ->upcoming()
                    ->limit(6)
            )
            ->columns([
                TextColumn::make('starts_on')
                    ->label('Date')
                    ->date('D d M')
                    ->weight('semibold'),
                TextColumn::make('display_title')
                    ->label('Event')
                    ->state(fn (TrainingEvent $record): string => $record->displayTitle())
                    ->description(fn (TrainingEvent $record): ?string => $record->disciplineName())
                    ->weight('bold'),
                TextColumn::make('venue')
                    ->label('Venue')
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('capacity_indicator')
                    ->label('Booked')
                    ->badge()
                    ->state(fn (TrainingEvent $record): string => $record->seats_taken.' / '.$record->capacity)
                    ->color(fn (TrainingEvent $record): string => match (true) {
                        $record->isFull() => 'danger',
                        $record->seatsLeft() <= 2 => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->emptyStateHeading('No upcoming dates scheduled')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->paginated(false);
    }
}
