<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WashJob;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

/**
 * Mengisi dan menjaga 7 Default Fields di semua tabel utama:
 *  - created_by / updated_by terisi otomatis dari pengguna yang login
 *  - data yang is_deleted = 1 tidak tampil di query biasa
 *  - delete() tidak menghapus baris, hanya mengubah is_deleted menjadi 1
 */
class DefaultFieldsServiceProvider extends ServiceProvider
{
    /** Tabel anak yang masih memakai data ini (meniru aturan RESTRICT di database). */
    private const DEPENDENTS = [
        User::class => [['vehicles', 'user_id'], ['bookings', 'user_id']],
        Service::class => [['bookings', 'service_id']],
        Vehicle::class => [['bookings', 'vehicle_id']],
        Booking::class => [['wash_jobs', 'booking_id'], ['booking_status_logs', 'booking_id']],
    ];

    public function boot(): void
    {
        $models = [User::class, Service::class, Vehicle::class, Booking::class, WashJob::class, BookingStatusLog::class];

        foreach ($models as $class) {
            $class::addGlobalScope('not_deleted', function (Builder $query) {
                $query->where($query->getModel()->getTable().'.is_deleted', 0);
            });

            $class::creating(function (Model $model) {
                $userId = auth()->id();
                if ($userId !== null) {
                    $model->created_by ??= $userId;
                    $model->updated_by ??= $userId;
                }
                if ($model instanceof BookingStatusLog && $model->updated_at === null) {
                    $model->updated_at = now()->toDateTimeString();
                }
            });

            $class::updating(function (Model $model) {
                $userId = auth()->id();
                if ($userId !== null && ! $model->isDirty('updated_by')) {
                    $model->updated_by = $userId;
                }
            });

            $class::deleting(function (Model $model) {
                foreach (self::DEPENDENTS[$model::class] ?? [] as [$table, $column]) {
                    if (DB::table($table)->where($column, $model->getKey())->exists()) {
                        throw new DomainException('Data ini masih dipakai di tabel '.$table.' dan tidak bisa dihapus.');
                    }
                }

                // Hapus lunak: tandai saja, jangan hapus barisnya.
                $model->forceFill(['is_deleted' => 1])->save();

                return false;
            });
        }
    }
}
