<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\WashJobStatus;
use App\Models\Booking;
use App\Models\WashJob;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = Booking::whereDate('booking_date', today())
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->count();

        $pending = Booking::where('status', BookingStatus::Pending->value)->count();

        $active = WashJob::where('status', WashJobStatus::InProgress->value)->count();

        $completedThisMonth = Booking::where('status', BookingStatus::Completed->value)
            ->whereBetween('booking_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->count();

        return [
            Stat::make('Booking hari ini', $today)
                ->description('Tidak termasuk yang dibatalkan')
                ->color('primary'),
            Stat::make('Menunggu konfirmasi', $pending)
                ->description('Perlu dikonfirmasi staff')
                ->color($pending > 0 ? 'warning' : 'gray'),
            Stat::make('Sedang dicuci', $active)
                ->description('Wash job berjalan')
                ->color('info'),
            Stat::make('Selesai bulan ini', $completedThisMonth)
                ->description(now()->translatedFormat('F Y'))
                ->color('success'),
        ];
    }
}
