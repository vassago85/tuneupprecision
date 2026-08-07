<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\TrainingEvent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming events · seats left';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TrainingEvent::query()
                    ->with('courseTemplate')
                    ->upcoming()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('courseTemplate.title')
                    ->label('Course')
                    ->weight('bold'),
                TextColumn::make('starts_on')
                    ->label('Date')
                    ->date('d M Y'),
                TextColumn::make('venue')
                    ->label('Venue'),
                TextColumn::make('seats_left')
                    ->label('Seats left')
                    ->badge()
                    ->state(fn (TrainingEvent $record): int => $record->seatsLeft())
                    ->color(fn (TrainingEvent $record): string => $record->isFull() ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->paginated(false);
    }
}
