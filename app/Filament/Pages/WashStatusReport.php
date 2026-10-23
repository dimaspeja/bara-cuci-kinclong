<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Enums\WashJobStatus;
use App\Filament\Support\ReportPage;
use App\Models\User;
use App\Models\WashJob;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class WashStatusReport extends ReportPage
{
    protected static ?string $title = 'Ringkasan Status Cuci';

    protected static ?string $navigationLabel = 'Ringkasan Status Cuci';

    protected static ?int $navigationSort = 3;

    protected function filterSchema(): array
    {
        return [
            DatePicker::make('from')->label('Dari tanggal')->live(),
            DatePicker::make('to')->label('Sampai tanggal')->live(),
            Select::make('staff')
                ->label('Staff')
                ->options(fn () => User::whereIn('role', [UserRole::Staff->value, UserRole::Admin->value])
                    ->orderBy('name')
                    ->pluck('name', 'id'))
                ->placeholder('Semua staff')
                ->live(),
        ];
    }

    public function columns(): array
    {
        return [
            'status' => 'Status',
            'count' => 'Jumlah',
            'avg' => 'Rata-rata durasi (menit)',
            'staff' => 'Staff',
        ];
    }

    public function rows(): array
    {
        $from = $this->data['from'] ?? null;
        $to = $this->data['to'] ?? null;
        $staff = $this->data['staff'] ?? null;

        $jobs = WashJob::query()
            ->with('staff')
            ->whereHas('booking', fn ($q) => $q
                ->when($from, fn ($qq) => $qq->whereDate('booking_date', '>=', $from))
                ->when($to, fn ($qq) => $qq->whereDate('booking_date', '<=', $to)))
            ->when($staff, fn ($q) => $q->where('staff_id', $staff))
            ->get();

        $order = array_map(fn (WashJobStatus $s) => $s->value, WashJobStatus::cases());

        return $jobs
            ->groupBy(fn (WashJob $job) => $job->status->value.'|'.($job->staff_id ?? 0))
            ->map(function ($group) {
                /** @var WashJob $first */
                $first = $group->first();
                $durations = $group->map(fn (WashJob $j) => $j->durationMinutes())->filter(fn ($d) => $d !== null);

                return [
                    '_order' => $first->status->value,
                    'status' => $first->status->getLabel(),
                    'count' => $group->count(),
                    'avg' => $durations->isNotEmpty() ? round($durations->avg(), 1) : '-',
                    'staff' => $first->staff?->name ?? '-',
                ];
            })
            ->sortBy(fn (array $row) => array_search($row['_order'], $order, true))
            ->map(fn (array $row) => collect($row)->except('_order')->all())
            ->values()
            ->all();
    }
}
