{{-- Menu utama, layanan pilihan, dan cara pesan. Variabel: $services. --}}
@php
    $secUser = auth()->user();
    $secStaff = (bool) ($secUser && $secUser->role?->isBackOffice() && ! $secUser->isAdmin());

    $menu = $secStaff
        ? [
            ['cog', 'Panel Admin', 'Kelola pesanan, layanan, dan laporan', url('/admin')],
            ['sparkles', 'Layanan', 'Lihat daftar layanan dan harga', route('services')],
        ]
        : [
            ['sparkles', 'Layanan', 'Lihat daftar layanan dan harga', route('services')],
            ['calendar', 'Booking', 'Pesan jadwal cuci kendaraan', route('bookings.create')],
            ['car', 'Kendaraan Saya', 'Simpan data mobil dan motor', route('vehicles.index')],
            ['list', 'Booking Saya', 'Pantau status pesananmu', route('bookings.index')],
        ];

    $steps = [
        ['1', 'Pilih layanan', 'Tentukan jenis cuci yang kamu butuhkan, lengkap dengan harga dan perkiraan durasi.'],
        ['2', 'Pilih jadwal', 'Pilih tanggal dan jam. Slot yang penuh otomatis tidak bisa dipilih.'],
        ['3', 'Datang dan bayar', 'Datang sesuai jadwal. Pembayaran dilakukan langsung di lokasi.'],
    ];
@endphp

{{-- MENU UTAMA --}}
<section class="mx-auto max-w-6xl px-4 py-10">
    <h2 class="text-2xl font-extrabold text-brand-dark">Menu Utama</h2>
    <p class="text-sm text-slate-500">Pilih menu untuk mulai memesan atau mengelola kendaraanmu.</p>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($menu as [$icon, $title, $desc, $href])
            <a href="{{ $href }}"
               class="group flex items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand/30 hover:shadow-md">
                <div>
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-brand">
                        <x-ikon :name="$icon" class="h-7 w-7" />
                    </span>
                    <h3 class="mt-3 font-bold text-brand-dark">{{ $title }}</h3>
                    <p class="text-sm text-slate-500">{{ $desc }}</p>
                </div>
                <x-ikon name="arrow" class="h-5 w-5 shrink-0 text-brand transition group-hover:translate-x-1" />
            </a>
        @endforeach
    </div>
</section>

{{-- LAYANAN PILIHAN --}}
<section class="mx-auto max-w-6xl px-4 pb-10">
    <div class="flex items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-brand-dark">Layanan Pilihan</h2>
            <p class="text-sm text-slate-500">Harga bersifat informasi. Pembayaran dilakukan langsung di lokasi.</p>
        </div>
        <a href="{{ route('services') }}" class="flex shrink-0 items-center gap-1 text-sm font-semibold text-brand hover:underline">
            Lihat Semua Layanan <x-ikon name="arrow" class="h-4 w-4" />
        </a>
    </div>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($services as $service)
            <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                <div class="flex h-28 items-center justify-center bg-gradient-to-br from-brand-dark to-brand text-white">
                    <x-ikon :name="str_contains(strtolower($service->name), 'detailing') ? 'sparkles' : 'car'" class="h-14 w-14 opacity-90" />
                </div>
                <div class="flex flex-1 flex-col p-4">
                    <h3 class="font-bold text-slate-800">{{ $service->name }}</h3>
                    <p class="mt-1 flex-1 text-sm text-slate-500">{{ $service->description }}</p>
                    <p class="mt-3 text-lg font-extrabold text-brand">{{ $service->priceLabel() }}</p>
                    <p class="text-xs text-slate-400">± {{ $service->duration_minutes }} menit</p>
                    <a href="{{ route('bookings.create', ['service' => $service->id]) }}"
                       class="mt-3 flex items-center justify-center gap-2 rounded-md bg-brand py-2 text-sm font-semibold text-white hover:bg-brand-dark">
                        <x-ikon name="calendar" class="h-4 w-4" /> Pesan
                    </a>
                </div>
            </div>
        @empty
            <p class="col-span-full text-slate-500">Belum ada layanan.</p>
        @endforelse
    </div>
</section>

{{-- CARA PESAN --}}
<section id="cara-pesan" class="mx-auto max-w-6xl scroll-mt-24 px-4 pb-6">
    <div class="grid gap-6 rounded-3xl bg-brand-soft p-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2.4fr)] lg:items-center lg:p-8">
        <div>
            <h2 class="text-2xl font-extrabold text-brand-dark">Cara Pesan</h2>
            <p class="mt-1 text-sm text-slate-600">Tiga langkah mudah, tanpa antre panjang.</p>
            <a href="{{ route('services') }}"
               class="mt-4 inline-flex items-center gap-2 rounded-full border border-brand px-5 py-2 text-sm font-semibold text-brand hover:bg-white">
                Lihat Semua Layanan <x-ikon name="arrow" class="h-4 w-4" />
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($steps as [$no, $title, $desc])
                <div class="rounded-2xl bg-white p-4 shadow-sm">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand font-bold text-white">{{ $no }}</span>
                    <h3 class="mt-2 font-bold text-brand-dark">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
