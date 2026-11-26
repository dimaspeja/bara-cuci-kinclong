<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\Service;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /** Tab cepat: Semua, Aktif, Nonaktif. */
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge(Service::count()),
            'aktif' => Tab::make('Aktif')
                ->badge(Service::where('is_active', true)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
            'nonaktif' => Tab::make('Nonaktif')
                ->badge(Service::where('is_active', false)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
        ];
    }
}
