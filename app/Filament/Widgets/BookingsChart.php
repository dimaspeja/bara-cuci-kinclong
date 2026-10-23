<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingsChart extends ChartWidget
{
    protected static ?string $heading = 'Booking 7 hari terakhir';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '260px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn (int $ago) => now()->subDays($ago)->startOfDay());

        $counts = Booking::whereBetween('booking_date', [
                $days->first()->toDateString(),
                $days->last()->toDateString(),
            ])
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->selectRaw('booking_date, count(*) as total')
            ->groupBy('booking_date')
            ->pluck('total', 'booking_date')
            ->mapWithKeys(fn ($total, $date) => [substr((string) $date, 0, 10) => (int) $total]);

        return [
            'datasets' => [
                [
                    'label' => 'Booking',
                    'data' => $days->map(fn ($day) => $counts[$day->toDateString()] ?? 0)->all(),
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->map(fn ($day) => $day->translatedFormat('d M'))->all(),
        ];
    }
}
