@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-gradient-to-br from-sky-600 to-sky-800 px-6 py-14 text-white sm:px-12">
        <p class="text-sm uppercase tracking-widest text-sky-200">Carwash &amp; Autocare</p>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Mobil bersih, perjalanan lebih nyaman.</h1>
        <p class="mt-3 max-w-xl text-sky-100">
            Pilih layanan, tentukan jadwal, dan datang saat giliran Anda. Tanpa antre panjang.
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            @auth
                <a href="{{ route('bookings.create') }}" class="rounded-md bg-white px-5 py-2 font-medium text-sky-700 hover:bg-sky-50">Booking sekarang</a>
            @else
                <a href="{{ route('register') }}" class="rounded-md bg-white px-5 py-2 font-medium text-sky-700 hover:bg-sky-50">Daftar &amp; booking</a>
                <a href="{{ route('login') }}" class="rounded-md border border-white/60 px-5 py-2 hover:bg-white/10">Masuk</a>
            @endauth
        </div>
    </section>

    <section class="mt-10">
        <h2 class="text-xl font-semibold">Layanan kami</h2>
        <p class="text-sm text-slate-500">Harga bersifat informasi. Pembayaran dilakukan langsung di lokasi.</p>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($services as $service)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <h3 class="font-semibold">{{ $service->name }}</h3>
                    <p class="mt-1 min-h-[3rem] text-sm text-slate-500">{{ $service->description }}</p>
                    <p class="mt-3 text-lg font-bold text-sky-600">{{ $service->priceLabel() }}</p>
                    <p class="text-xs text-slate-400">± {{ $service->duration_minutes }} menit</p>
                </div>
            @empty
                <p class="text-slate-500">Belum ada layanan.</p>
            @endforelse
        </div>
    </section>
@endsection
