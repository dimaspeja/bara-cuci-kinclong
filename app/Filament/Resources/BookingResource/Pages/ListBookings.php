<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    /** Tab cepat untuk pekerjaan staff sehari-hari. */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'pending' => Tab::make('Menunggu')
                ->badge(Booking::where('status', BookingStatus::Pending->value)->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BookingStatus::Pending->value)),
            'today' => Tab::make('Hari ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('booking_date', today())),
            'in_progress' => Tab::make('Sedang dicuci')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BookingStatus::InProgress->value)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
