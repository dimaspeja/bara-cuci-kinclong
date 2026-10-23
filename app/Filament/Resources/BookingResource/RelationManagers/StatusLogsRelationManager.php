<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use App\Enums\BookingStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StatusLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'statusLogs';

    protected static ?string $title = 'Riwayat Status';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('to_status')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('from_status')
                    ->label('Dari')
                    ->formatStateUsing(fn ($state) => $state ? BookingStatus::from($state)->label() : '-'),
                Tables\Columns\TextColumn::make('to_status')
                    ->label('Menjadi')
                    ->formatStateUsing(fn ($state) => BookingStatus::from($state)->label()),
                Tables\Columns\TextColumn::make('actor.name')->label('Oleh'),
            ])
            ->defaultSort('id', 'desc')
            ->paginated(false);
    }
}
