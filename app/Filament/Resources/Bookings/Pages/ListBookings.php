<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'upcoming' => Tab::make('Upcoming')
                ->badge(fn (): ?string => self::countBadge(Booking::query()->upcoming()))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->upcoming()),
            'to-pay' => Tab::make('To pay')
                ->badge(fn (): ?string => self::countBadge(Booking::query()->where('status', BookingStatus::Pending)))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', BookingStatus::Pending)),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->completed()),
            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', BookingStatus::Cancelled)),
            'all' => Tab::make('All'),
        ];
    }

    /**
     * @param  Builder<Booking>  $query
     */
    private static function countBadge(Builder $query): ?string
    {
        $count = $query->count();

        return $count > 0 ? (string) $count : null;
    }
}
