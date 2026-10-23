<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 25)->unique(); // BKG-YYYYMMDD-0001
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();

            // Salinan dari layanan saat booking, supaya laporan tetap benar jika harga berubah.
            $table->decimal('service_price', 12, 2);
            $table->unsignedSmallInteger('duration_minutes');

            $table->date('booking_date');
            $table->time('start_time');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])
                ->default('pending');
            $table->string('cancel_reason')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['booking_date', 'status']);
            $table->index(['booking_date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
