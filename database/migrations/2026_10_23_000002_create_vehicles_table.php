<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('plate_number', 15);
            $table->enum('type', ['car', 'motorcycle', 'suv', 'other']);
            $table->string('brand', 50);
            $table->string('model', 50)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plate_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
