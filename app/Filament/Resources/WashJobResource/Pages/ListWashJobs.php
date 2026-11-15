<?php

namespace App\Filament\Resources\WashJobResource\Pages;

use App\Enums\WashJobStatus;
use App\Filament\Resources\WashJobResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWashJobs extends ListRecords
{
    protected static string $resource = WashJobResource::class;

    public function getTabs(): array
    {
        return [
            'queue' => Tab::make('Antrian')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    WashJobStatus::Waiting->value,
                    WashJobStatus::InProgress->value,
                ])),
            'done' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', WashJobStatus::Done->value)),
            'all' => Tab::make('Semua'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'queue';
    }
}
