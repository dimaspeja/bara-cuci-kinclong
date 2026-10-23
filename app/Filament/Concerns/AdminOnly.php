<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Pakai di Resource yang hanya boleh dikelola Admin.
 * Staff tetap bisa masuk panel, tetapi resource ini tidak muncul dan URL-nya ditolak (403).
 * Tidak ada penghapusan lewat panel: nonaktifkan data, jangan hapus.
 */
trait AdminOnly
{
    protected static function isAdminUser(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canViewAny(): bool
    {
        return static::isAdminUser();
    }

    public static function canView(Model $record): bool
    {
        return static::isAdminUser();
    }

    public static function canCreate(): bool
    {
        return static::isAdminUser();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isAdminUser();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
