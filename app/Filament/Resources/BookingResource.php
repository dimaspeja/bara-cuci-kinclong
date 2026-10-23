<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use App\Services\BookingService;
use App\Support\Format;
use Closure;
use DomainException;
use Filament\Forms;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Booking';

    protected static ?string $modelLabel = 'Booking';

    protected static ?string $pluralModelLabel = 'Booking';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?int $navigationSort = 1;

    // Booking dibuat pelanggan dan diubah lewat aksi alur status, bukan lewat form.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
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

    public static function getNavigationBadge(): ?string
    {
        $pending = Booking::where('status', BookingStatus::Pending->value)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Booking')
                ->schema([
                    TextEntry::make('code')->label('Kode'),
                    TextEntry::make('status')->label('Status')->badge(),
                    TextEntry::make('booking_date')->label('Tanggal')->date('d M Y'),
                    TextEntry::make('start_time')
                        ->label('Jam')
                        ->formatStateUsing(fn ($state) => substr((string) $state, 0, 5)),
                    TextEntry::make('notes')->label('Catatan')->placeholder('-'),
                    TextEntry::make('cancel_reason')
                        ->label('Alasan batal')
                        ->placeholder('-')
                        ->visible(fn (Booking $record) => filled($record->cancel_reason)),
                ])
                ->columns(3),
            Section::make('Pelanggan & kendaraan')
                ->schema([
                    TextEntry::make('user.name')->label('Pelanggan'),
                    TextEntry::make('user.phone')->label('Telepon')->placeholder('-'),
                    TextEntry::make('vehicle.plate_number')->label('Plat'),
                    TextEntry::make('vehicle.brand')->label('Merek'),
                ])
                ->columns(4),
            Section::make('Layanan')
                ->schema([
                    TextEntry::make('service.name')->label('Layanan'),
                    TextEntry::make('service_price')
                        ->label('Harga saat booking')
                        ->formatStateUsing(fn ($state) => Format::rupiah($state)),
                    TextEntry::make('duration_minutes')->label('Durasi')->suffix(' mnt'),
                    TextEntry::make('washJob.staff.name')->label('Staff')->placeholder('-'),
                ])
                ->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'vehicle', 'service']))
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('booking_date')->label('Tanggal')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam')
                    ->formatStateUsing(fn ($state) => substr((string) $state, 0, 5))
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('vehicle.plate_number')->label('Plat')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan'),
                Tables\Columns\TextColumn::make('service_price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => Format::rupiah($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()->sortable(),
            ])
            ->defaultSort('booking_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(BookingStatus::class),
                Tables\Filters\Filter::make('today')
                    ->label('Hari ini')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->whereDate('booking_date', today())),
                Tables\Filters\Filter::make('period')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('booking_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('booking_date', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
                ...static::workflowActions(Tables\Actions\Action::class),
            ]);
    }

    /**
     * Aksi alur status: Konfirmasi, Mulai cuci, Selesai, Batalkan.
     * Dipakai di tabel (Tables\Actions\Action) dan di halaman detail (Filament\Actions\Action).
     *
     * @param  class-string  $actionClass
     * @return array<int, mixed>
     */
    public static function workflowActions(string $actionClass): array
    {
        return [
            $actionClass::make('confirm')
                ->label('Konfirmasi')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn (Booking $record) => $record->status === BookingStatus::Pending)
                ->action(fn (Booking $record) => self::run(
                    fn () => app(BookingService::class)->confirm($record, auth()->user()),
                    'Booking dikonfirmasi.'
                )),

            $actionClass::make('start')
                ->label('Mulai cuci')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (Booking $record) => $record->status === BookingStatus::Confirmed)
                ->action(fn (Booking $record) => self::run(
                    fn () => app(BookingService::class)->start($record, auth()->user()),
                    'Pencucian dimulai.'
                )),

            $actionClass::make('finish')
                ->label('Selesai')
                ->icon('heroicon-o-flag')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Booking $record) => $record->status === BookingStatus::InProgress)
                ->action(fn (Booking $record) => self::run(
                    fn () => app(BookingService::class)->finish($record, auth()->user()),
                    'Pencucian selesai.'
                )),

            $actionClass::make('cancel')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Batalkan booking')
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Alasan pembatalan')
                        ->required()
                        ->maxLength(255),
                ])
                ->visible(fn (Booking $record) => in_array($record->status, [BookingStatus::Pending, BookingStatus::Confirmed], true))
                ->action(fn (Booking $record, array $data) => self::run(
                    fn () => app(BookingService::class)->cancel($record, auth()->user(), $data['reason']),
                    'Booking dibatalkan.'
                )),
        ];
    }

    /** Jalankan aksi dan tampilkan notifikasi sukses / gagal. */
    protected static function run(Closure $callback, string $successMessage): void
    {
        try {
            $callback();

            Notification::make()->title($successMessage)->success()->send();
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        } catch (ValidationException $e) {
            Notification::make()->title((string) collect($e->errors())->flatten()->first())->danger()->send();
        }
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StatusLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }
}
