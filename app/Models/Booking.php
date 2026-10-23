<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Support\Format;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'booking_date' => 'date:Y-m-d',
            'service_price' => 'decimal:2',
            'duration_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function washJob(): HasOne
    {
        return $this->hasOne(WashJob::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    /** Jam mulai dalam format HH:MM. */
    public function timeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->booking_date->format('Y-m-d').' '.$this->timeLabel());
    }

    public function priceLabel(): string
    {
        return Format::rupiah($this->service_price);
    }
}
