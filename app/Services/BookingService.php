<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\WashJobStatus;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WashJob;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Semua perubahan status booking lewat sini, supaya booking, wash job,
 * dan log status selalu berubah bersama dalam satu transaksi database.
 */
class BookingService
{
    /** @return array<int, string> contoh: ['08:00', '09:00', ...] */
    public function slots(): array
    {
        $open = (int) config('bara.open_hour', 8);
        $close = (int) config('bara.close_hour', 17);

        return collect(range($open, $close - 1))
            ->map(fn (int $hour) => sprintf('%02d:00', $hour))
            ->all();
    }

    /**
     * Buat booking baru (status pending).
     *
     * @param  array{service_id:int, vehicle_id:int, booking_date:string, start_time:string, notes?:?string}  $data
     */
    public function create(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            $vehicle = Vehicle::where('user_id', $user->id)->find($data['vehicle_id']);
            if (! $vehicle) {
                throw ValidationException::withMessages(['vehicle_id' => 'Kendaraan tidak ditemukan.']);
            }

            $service = Service::where('is_active', true)->find($data['service_id']);
            if (! $service) {
                throw ValidationException::withMessages(['service_id' => 'Layanan tidak tersedia.']);
            }

            $time = $data['start_time'];
            if (! in_array($time, $this->slots(), true)) {
                throw ValidationException::withMessages(['start_time' => 'Jam yang dipilih di luar jam operasional.']);
            }

            $date = Carbon::parse($data['booking_date'])->toDateString();
            if (Carbon::parse("$date $time")->isPast()) {
                throw ValidationException::withMessages(['start_time' => 'Waktu yang dipilih sudah lewat.']);
            }

            // Cegah double booking: hitung booking aktif pada slot yang sama (dengan kunci baris).
            $taken = Booking::where('booking_date', $date)
                ->where('start_time', $time.':00')
                ->where('status', '!=', BookingStatus::Cancelled->value)
                ->lockForUpdate()
                ->count();

            if ($taken >= (int) config('bara.slot_capacity', 3)) {
                throw ValidationException::withMessages(['start_time' => 'Slot jam tersebut sudah penuh. Pilih jam lain.']);
            }

            $booking = Booking::create([
                'code' => $this->nextCode(),
                'user_id' => $user->id,
                'vehicle_id' => $vehicle->id,
                'service_id' => $service->id,
                'service_price' => $service->price,       // salinan harga saat booking
                'duration_minutes' => $service->duration_minutes,
                'booking_date' => $date,
                'start_time' => $time.':00',
                'status' => BookingStatus::Pending,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->log($booking, null, BookingStatus::Pending, $user);

            return $booking;
        });
    }

    public function confirm(Booking $booking, User $actor): Booking
    {
        DB::transaction(function () use ($booking, $actor) {
            $b = $this->move($booking, $actor, BookingStatus::Confirmed);

            WashJob::firstOrCreate(
                ['booking_id' => $b->id],
                ['status' => WashJobStatus::Waiting],
            );
        });

        return $booking->refresh();
    }

    public function start(Booking $booking, User $actor): Booking
    {
        DB::transaction(function () use ($booking, $actor) {
            $b = $this->move($booking, $actor, BookingStatus::InProgress);

            WashJob::updateOrCreate(
                ['booking_id' => $b->id],
                [
                    'status' => WashJobStatus::InProgress,
                    'staff_id' => $actor->id,
                    'started_at' => now(),
                ],
            );
        });

        return $booking->refresh();
    }

    public function finish(Booking $booking, User $actor): Booking
    {
        DB::transaction(function () use ($booking, $actor) {
            $b = $this->move($booking, $actor, BookingStatus::Completed);

            WashJob::where('booking_id', $b->id)->firstOrFail()->update([
                'status' => WashJobStatus::Done,
                'finished_at' => now(),
            ]);
        });

        return $booking->refresh();
    }

    /**
     * Pelanggan hanya boleh membatalkan booking miliknya yang masih pending.
     * Staff/Admin boleh membatalkan booking pending atau confirmed.
     */
    public function cancel(Booking $booking, User $actor, ?string $reason = null): Booking
    {
        if ($actor->isCustomer()) {
            if ($booking->user_id !== $actor->id) {
                throw new DomainException('Booking ini bukan milik Anda.');
            }
            if ($booking->status !== BookingStatus::Pending) {
                throw new DomainException('Booking yang sudah dikonfirmasi tidak bisa dibatalkan sendiri. Hubungi kami.');
            }
        }

        DB::transaction(function () use ($booking, $actor, $reason) {
            $b = $this->move($booking, $actor, BookingStatus::Cancelled, $reason);

            // Antrian cuci yang belum dimulai dihapus; booking-nya tetap tersimpan (status dibatalkan).
            WashJob::where('booking_id', $b->id)
                ->where('status', WashJobStatus::Waiting->value)
                ->delete();
        });

        return $booking->refresh();
    }

    /** Ubah status dengan validasi alur + tulis log. Dipanggil di dalam transaksi. */
    private function move(Booking $booking, User $actor, BookingStatus $to, ?string $reason = null): Booking
    {
        $b = Booking::whereKey($booking->getKey())->lockForUpdate()->firstOrFail();

        if (! $b->status->canTransitionTo($to)) {
            throw new DomainException(
                "Status \"{$b->status->label()}\" tidak bisa diubah menjadi \"{$to->label()}\"."
            );
        }

        $from = $b->status;
        $b->status = $to;
        if ($to === BookingStatus::Cancelled) {
            $b->cancel_reason = $reason;
        }
        $b->save();

        $this->log($b, $from, $to, $actor);

        return $b;
    }

    private function log(Booking $booking, ?BookingStatus $from, BookingStatus $to, User $actor): void
    {
        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_by' => $actor->id,
            'created_at' => now(),
        ]);
    }

    /** Kode booking: BKG-YYYYMMDD-0001 (urutan per hari pembuatan). */
    private function nextCode(): string
    {
        $prefix = 'BKG-'.now()->format('Ymd').'-';

        $last = Booking::where('code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('code')
            ->value('code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
