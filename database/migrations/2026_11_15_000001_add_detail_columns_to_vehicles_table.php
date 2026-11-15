<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('color', 30)->nullable()->after('model');
            $table->unsignedSmallInteger('year')->nullable()->after('color');
            $table->string('note', 255)->nullable()->after('year');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['color', 'year', 'note']);
        });
    }
};
