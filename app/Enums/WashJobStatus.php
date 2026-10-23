<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WashJobStatus: string implements HasLabel, HasColor
{
    case Waiting = 'waiting';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Waiting => 'Menunggu',
            self::InProgress => 'Sedang Dicuci',
            self::Done => 'Selesai',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Waiting => 'gray',
            self::InProgress => 'primary',
            self::Done => 'success',
        };
    }
}
