<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\TrainingEvents\TrainingEventResource;
use App\Models\TrainingEvent;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingTrainingWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 2];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Upcoming training')
            ->query(
                TrainingEvent::query()
                    ->with('courseTemplate.trainingType')
                    ->onCoursesPage()
                    ->limit(5)
            )
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->link()
                    ->url(TrainingEventResource::getUrl('index')),
            ])
            ->recordUrl(fn (TrainingEvent $record): string => TrainingEventResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('starts_on')
                    ->label('Date')
                    ->date('D d M')
                    ->weight('semibold'),
                TextColumn::make('display_title')
                    ->label('Course')
                    ->state(fn (TrainingEvent $record): string => $record->displayTitle())
                    ->description(fn (TrainingEvent $record): ?string => $record->disciplineName())
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('venue')
                    ->color('gray')
                    ->visibleFrom('md'),
                ViewColumn::make('seats')
                    ->label('Seats')
                    ->view('filament.tables.columns.seat-fill'),
            ])
            ->emptyStateHeading('No upcoming dates scheduled')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->paginated(false);
    }
}
