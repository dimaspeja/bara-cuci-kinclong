<?php

return [
    // Maksimum booking aktif (selain dibatalkan) pada satu slot jam yang sama.
    'slot_capacity' => (int) env('SLOT_CAPACITY', 3),

    // Jam operasional. Slot dimulai tiap jam dari open_hour sampai close_hour - 1.
    'open_hour' => (int) env('OPEN_HOUR', 8),
    'close_hour' => (int) env('CLOSE_HOUR', 17),

    'timezone' => env('BARA_TIMEZONE', 'Asia/Jakarta'),
];
