<?php

namespace App\Filament\Pages;

use App\Enums\BookingStatus;
use App\Filament\Support\ReportPage;
use App\Models\Booking;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class BookingsByPeriodReport extends ReportPage
{
    protected static ?string $title = 'Booking per Periode';

    protected static ?string $navigationLabel = 'Booking per Periode';

    protected static ?int $navigationSort = 1;

    protected function defaultFilters(): array
    {
        return [...parent::defaultFilters(), 'group' => 'day'];
    }

    protected function filterSchema(): array
    {
        return [
            DatePicker::make('from')->label('Dari tanggal')->live(),
            DatePicker::make('to')->label('Sampai tanggal')->live(),
            Select::make('group')
                ->label('Kelompokkan')
                ->options(['day' => 'Per hari', 'month' => 'Per bulan'])
                ->selectablePlaceholder(false)
                ->live(),
        ];
    }

    public function columns(): array
    {
        return [
            'period' => 'Periode',
            'total' => 'Total',
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'in_progress' => 'Sedang dicuci',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    public function rows(): array
    {
        $from = $this->data['from'] ?? null;
        $to = $this->data['to'] ?? null;
        $format = ($this->data['group'] ?? 'day') === 'month' ? 'Y-m' : 'Y-m-d';

        $bookings = Booking::query()
            ->when($from, fn ($q) => $q->whereDate('booking_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('booking_date', '<=', $to))
            ->get(['id', 'booking_date', 'status']);

        return $bookings
            ->groupBy(fn (Booking $b) => $b->booking_date->format($format))
            ->sortKeys()
            ->map(function ($group, $period) {
                $count = fn (BookingStatus $status) => $group->filter(fn (Booking $b) => $b->status === $status)->count();

                return [
                    'period' => (string) $period,
                    'total' => $group->count(),
                    'pending' => $count(BookingStatus::Pending),
                    'confirmed' => $count(BookingStatus::Confirmed),
                    'in_progress' => $count(BookingStatus::InProgress),
                    'completed' => $count(BookingStatus::Completed),
                    'cancelled' => $count(BookingStatus::Cancelled),
                ];
            })
            ->values()
            ->all();
    }
}
