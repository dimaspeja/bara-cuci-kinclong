<?php

namespace App\Models;

use App\Enums\WashJobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WashJob extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => WashJobStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /** Durasi pencucian dalam menit (null jika belum selesai). */
    public function durationMinutes(): ?int
    {
        if (! $this->started_at || ! $this->finished_at) {
            return null;
        }

        return (int) $this->started_at->diffInMinutes($this->finished_at);
    }
}
