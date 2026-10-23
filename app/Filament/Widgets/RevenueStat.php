<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Support\Format;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueStat extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Harga memakai salinan di booking (service_price), jadi aman jika harga layanan berubah.
        $revenue = Booking::where('status', BookingStatus::Completed->value)
            ->whereBetween('booking_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('service_price');

        return [
            Stat::make('Estimasi pendapatan bulan ini', Format::rupiah($revenue))
                ->description('Total harga booking selesai. Hanya estimasi, tidak ada data pembayaran.')
                ->color('success'),
        ];
    }
}
