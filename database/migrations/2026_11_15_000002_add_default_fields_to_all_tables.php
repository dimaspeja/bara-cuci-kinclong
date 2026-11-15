<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 7 Default Fields di setiap tabel utama:
 *   id (PK), status, is_deleted, created_by, created_at, updated_by, updated_at
 *
 * id, created_at, updated_at sudah ada dari Laravel. Migrasi ini menambah sisanya.
 * Tabel bookings dan wash_jobs sudah punya kolom "status" untuk alur kerja,
 * jadi status default-nya diberi nama "record_status".
 */
return new class extends Migration
{
    private array $tables = ['users', 'services', 'vehicles', 'bookings', 'wash_jobs', 'booking_status_logs'];

    private array $workflowTables = ['bookings', 'wash_jobs'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            $statusColumn = in_array($name, $this->workflowTables, true) ? 'record_status' : 'status';

            Schema::table($name, function (Blueprint $table) use ($name, $statusColumn) {
                if (! Schema::hasColumn($name, $statusColumn)) {
                    $table->unsignedTinyInteger($statusColumn)->default(1); // 1 = aktif, 0 = nonaktif
                }
                if (! Schema::hasColumn($name, 'is_deleted')) {
                    $table->unsignedTinyInteger('is_deleted')->default(0);  // 1 = sudah dihapus
                }
                if (! Schema::hasColumn($name, 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
                if (! Schema::hasColumn($name, 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable();
                }
                if (! Schema::hasColumn($name, 'updated_at')) {
                    $table->timestamp('updated_at')->nullable(); // booking_status_logs belum punya
                }
            });
        }

        // Kendaraan yang dihapus hanya ditandai is_deleted = 1 (plat tetap tersimpan),
        // jadi keunikan plat dijaga lewat validasi (hanya data yang belum dihapus).
        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('user_id', 'vehicles_user_id_index'); // dibutuhkan foreign key user_id
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique('vehicles_user_id_plate_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unique(['user_id', 'plate_number']);
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('vehicles_user_id_index');
        });

        foreach ($this->tables as $name) {
            $statusColumn = in_array($name, $this->workflowTables, true) ? 'record_status' : 'status';
            $columns = [$statusColumn, 'is_deleted', 'created_by', 'updated_by'];
            if ($name === 'booking_status_logs') {
                $columns[] = 'updated_at';
            }

            Schema::table($name, function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
