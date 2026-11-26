<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Support\Format;
use DomainException;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

class ServiceResource extends Resource
{
    use AdminOnly;

    // Admin boleh menghapus (hapus lunak: is_deleted = 1). Layanan yang sudah
    // dipakai booking ditolak oleh DefaultFieldsServiceProvider.
    public static function canDelete(Model $record): bool
    {
        return static::isAdminUser();
    }

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Layanan';

    protected static ?string $modelLabel = 'Layanan';

    protected static ?string $pluralModelLabel = 'Layanan';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama layanan')
                    ->required()
                    ->maxLength(100)
                    // Unik di antara layanan yang belum dihapus (nama bekas layanan terhapus boleh dipakai lagi).
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('is_deleted', 0),
                    ),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->label('Harga (informasi)')
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->maxValue(100000000)
                    ->required(),
                Forms\Components\TextInput::make('duration_minutes')
                    ->label('Durasi (menit)')
                    ->numeric()
                    ->minValue(5)
                    ->maxValue(1000)
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif (tampil ke pelanggan)')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Layanan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => Format::rupiah($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->suffix(' mnt')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Booking')
                    ->counts('bookings')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->defaultSort('price')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('hapus')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus layanan')
                    ->modalDescription('Layanan disembunyikan dari daftar. Layanan yang sudah punya booking tidak bisa dihapus; nonaktifkan saja.')
                    ->visible(fn (Service $record) => static::canDelete($record))
                    ->action(function (Service $record): void {
                        try {
                            $record->delete();

                            Notification::make()->title('Layanan dihapus.')->success()->send();
                        } catch (DomainException $e) {
                            Notification::make()
                                ->title('Layanan tidak bisa dihapus')
                                ->body($e->getMessage().' Nonaktifkan saja agar tidak tampil ke pelanggan.')
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
