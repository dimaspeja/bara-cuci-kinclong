<?php

namespace App\Support;

final class Format
{
    public static function rupiah(float|int|string|null $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}
