<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel, HasColor
{
    case Customer = 'customer';
    case Staff = 'staff';
    case Admin = 'admin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Customer => 'Pelanggan',
            self::Staff => 'Staff',
            self::Admin => 'Admin',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Customer => 'gray',
            self::Staff => 'info',
            self::Admin => 'warning',
        };
    }

    /** Staff dan Admin memakai panel Filament. */
    public function isBackOffice(): bool
    {
        return $this !== self::Customer;
    }
}
