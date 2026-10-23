<?php

namespace App\Filament\Pages;

use App\Enums\BookingStatus;
use App\Filament\Support\ReportPage;
use App\Models\Booking;
use App\Support\Format;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class ServicePopularityReport extends ReportPage
{
    protected static ?string $title = 'Layanan Terpopuler';

    protected static ?string $navigationLabel = 'Layanan Terpopuler';

    protected static ?int $navigationSort = 2;

    protected function filterSchema(): array
    {
        return [
            DatePicker::make('from')->label('Dari tanggal')->live(),
            DatePicker::make('to')->label('Sampai tanggal')->live(),
            Select::make('status')
                ->label('Status booking')
                ->options(BookingStatus::class)
                ->placeholder('Semua status')
                ->live(),
        ];
    }

    public function columns(): array
    {
        return [
            'rank' => '#',
            'service' => 'Layanan',
            'total' => 'Jumlah booking',
            'completed' => 'Selesai',
            'revenue' => 'Estimasi pendapatan (selesai)',
        ];
    }

    public function rows(): array
    {
        $from = $this->data['from'] ?? null;
        $to = $this->data['to'] ?? null;
        $status = $this->data['status'] ?? null;
        $done = BookingStatus::Completed->value;

        $result = Booking::query()
            ->join('services', 'services.id', '=', 'bookings.service_id')
            ->when($from, fn ($q) => $q->whereDate('bookings.booking_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('bookings.booking_date', '<=', $to))
            ->when($status, fn ($q) => $q->where('bookings.status', $status))
            ->selectRaw(
                "services.name as service_name,
                 count(*) as total,
                 sum(case when bookings.status = '{$done}' then 1 else 0 end) as completed,
                 sum(case when bookings.status = '{$done}' then bookings.service_price else 0 end) as revenue"
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total')
            ->get();

        return $result
            ->values()
            ->map(fn ($row, int $i) => [
                'rank' => $i + 1,
                'service' => $row->service_name,
                'total' => (int) $row->total,
                'completed' => (int) $row->completed,
                'revenue' => Format::rupiah($row->revenue),
            ])
            ->all();
    }
}
