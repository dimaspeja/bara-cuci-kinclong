<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Read-only: kendaraan dikelola pelanggan sendiri. */
class VehicleResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Kendaraan';

    protected static ?string $modelLabel = 'Kendaraan';

    protected static ?string $pluralModelLabel = 'Kendaraan';

    protected static ?int $navigationSort = 3;

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

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->columns([
                Tables\Columns\TextColumn::make('plate_number')->label('Plat')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pemilik')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => Vehicle::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('brand')->label('Merek')->searchable(),
                Tables\Columns\TextColumn::make('model')->label('Model')->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('Jenis')->options(Vehicle::TYPES),
            ])
            ->defaultSort('plate_number');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
        ];
    }
}
