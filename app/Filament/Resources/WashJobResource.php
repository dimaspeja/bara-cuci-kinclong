<?php

namespace App\Filament\Resources;

use App\Enums\WashJobStatus;
use App\Filament\Resources\WashJobResource\Pages;
use App\Models\WashJob;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Antrian dan riwayat pencucian (read-only). Aksinya dilakukan dari menu Booking. */
class WashJobResource extends Resource
{
    protected static ?string $model = WashJob::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Antrian Cuci';

    protected static ?string $modelLabel = 'Wash job';

    protected static ?string $pluralModelLabel = 'Antrian Cuci';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['booking.user', 'booking.vehicle', 'booking.service', 'staff']))
            ->columns([
                Tables\Columns\TextColumn::make('booking.code')->label('Kode')->searchable(),
                Tables\Columns\TextColumn::make('booking.booking_date')->label('Tanggal')->date('d M Y'),
                Tables\Columns\TextColumn::make('booking.start_time')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state) => substr((string) $state, 0, 5)),
                Tables\Columns\TextColumn::make('booking.user.name')->label('Pelanggan'),
                Tables\Columns\TextColumn::make('booking.vehicle.plate_number')->label('Plat'),
                Tables\Columns\TextColumn::make('booking.service.name')->label('Layanan'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('staff.name')->label('Staff')->placeholder('-'),
                Tables\Columns\TextColumn::make('started_at')->label('Mulai')->dateTime('d M H:i')->placeholder('-'),
                Tables\Columns\TextColumn::make('finished_at')->label('Selesai')->dateTime('d M H:i')->placeholder('-'),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Durasi')
                    ->state(fn (WashJob $record) => $record->durationMinutes() !== null ? $record->durationMinutes().' mnt' : '-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Status')->options(WashJobStatus::class),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWashJobs::route('/'),
        ];
    }
}
