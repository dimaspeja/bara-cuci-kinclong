<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bara Cuci Kinclong')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { DEFAULT: '#0b57c9', dark: '#0a2f6b', light: '#5cc8ff', soft: '#eaf3ff' } } } }
        };
    </script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
@php
    $authUser = auth()->user();
    $isBackOffice = (bool) ($authUser && $authUser->role?->isBackOffice());
    $isAdmin = (bool) ($authUser && $authUser->isAdmin());
    $canBook = $authUser && (! $isBackOffice || $isAdmin);

    $links = [
        ['Beranda', $canBook ? route('home') : route('landing'), request()->routeIs('landing', 'home')],
        ['Layanan', route('services'), request()->routeIs('services')],
    ];
    if ($canBook) {
        $links[] = ['Booking', route('bookings.create'), request()->routeIs('bookings.create')];
        $links[] = ['Booking Saya', route('bookings.index'), request()->routeIs('bookings.index')];
        $links[] = ['Kendaraan', route('vehicles.index'), request()->routeIs('vehicles.*')];
    }
    $links[] = ['Cara Pesan', route('landing').'#cara-pesan', false];
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
    <nav class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3 text-sm">
        <a href="{{ route('landing') }}" class="shrink-0">
            <x-logo />
        </a>

        <div class="order-3 flex w-full flex-wrap gap-x-5 gap-y-1 lg:order-none lg:w-auto">
            @foreach ($links as [$label, $href, $active])
                <a href="{{ $href }}"
                   class="border-b-2 py-1 font-medium {{ $active ? 'border-brand text-brand' : 'border-transparent text-slate-700 hover:text-brand' }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('services') }}" class="order-4 flex-1 lg:order-none lg:max-w-xs">
            <label class="flex items-center gap-2 rounded-full border border-sky-200 bg-brand-soft px-4 py-2">
                <x-ikon name="search" class="h-4 w-4 text-brand" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari layanan..."
                       class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400">
            </label>
        </form>

        <div class="ml-auto flex items-center gap-3">
            @auth
                @if ($isBackOffice)
                    <a href="/admin" class="rounded-full bg-brand px-4 py-1.5 font-semibold text-white hover:bg-brand-dark">Panel Admin</a>
                @endif
                <span class="hidden items-center gap-2 text-slate-600 sm:flex">
                    <x-ikon name="user" class="h-5 w-5" /> {{ $authUser->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-full border border-slate-300 px-4 py-1.5 hover:bg-slate-100">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-2 font-medium text-slate-700 hover:text-brand">
                    <x-ikon name="user" class="h-5 w-5" /> Masuk
                </a>
                <a href="{{ route('register') }}" class="rounded-full bg-brand px-4 py-1.5 font-semibold text-white hover:bg-brand-dark">Daftar</a>
            @endauth
        </div>
    </nav>
</header>

@if (session('status') || $errors->any())
    <div class="mx-auto max-w-6xl space-y-3 px-4 pt-6">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc space-y-0.5 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif

@hasSection('fullwidth')
    <main>@yield('content')</main>
@else
    <main class="mx-auto max-w-5xl px-4 py-8">@yield('content')</main>
@endif

<footer class="mt-10 bg-brand-dark text-slate-200">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-6 px-4 py-8 text-sm">
        <a href="{{ route('landing') }}">
            <x-logo variant="footer" />
        </a>

        <p class="max-w-md text-xs leading-relaxed text-slate-300">
            Tempat cuci kendaraan dengan sistem booking online.
            Buka {{ sprintf('%02d.00', config('bara.open_hour', 8)) }}–{{ sprintf('%02d.00', config('bara.close_hour', 17)) }} WIB.
            Pembayaran dilakukan langsung di lokasi.
        </p>

        <p class="text-xs text-slate-400">© {{ date('Y') }} Bara Cuci Kinclong. Hak cipta dilindungi.</p>
    </div>
</footer>
</body>
</html>
