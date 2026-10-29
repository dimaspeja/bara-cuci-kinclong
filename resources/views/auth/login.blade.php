@extends('layouts.app')

@section('title', 'Masuk · Bara Cuci Kinclong')

@section('content')
    {{-- Warna utama: navy #0b2d4b dan cyan #22b5d1. Ganti di sini jika ingin mengubah tema. --}}
    <div class="mx-auto max-w-md rounded-2xl bg-[#0b2d4b] p-5 sm:p-6">
        <div class="rounded-xl bg-white px-6 pb-6 pt-8 sm:px-8">

            {{-- 1. Logo dan judul --}}
            <div class="flex flex-col items-center">
                <a href="{{ route('landing') }}"><x-logo variant="login" /></a>
                <h1 class="mt-3 font-serif text-3xl font-bold text-[#0b2d4b]">Masuk ke Akun</h1>
            </div>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
                @csrf

                {{-- 2. Kolom email --}}
                <div>
                    <label for="email" class="text-sm font-semibold text-[#0b2d4b]">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@email.com"
                           class="mt-1 w-full rounded-md border border-slate-200 bg-slate-100 px-4 py-2.5 text-slate-700 placeholder-slate-400 focus:border-[#22b5d1] focus:outline-none focus:ring-2 focus:ring-[#22b5d1]/30">
                </div>

                {{-- 3. Kolom kata sandi --}}
                <div>
                    <label for="password" class="text-sm font-semibold text-[#0b2d4b]">Kata sandi</label>
                    <input id="password" name="password" type="password" required placeholder="••••••••"
                           class="mt-1 w-full rounded-md border border-slate-200 bg-slate-100 px-4 py-2.5 text-slate-700 placeholder-slate-400 focus:border-[#22b5d1] focus:outline-none focus:ring-2 focus:ring-[#22b5d1]/30">
                </div>

                {{-- 4. Ingat saya (tautan "Lupa kata sandi?" belum ada fiturnya) --}}
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-slate-400 text-[#22b5d1]">
                    Ingat saya
                </label>

                {{-- 5. Tombol Masuk --}}
                <button class="w-full rounded-md bg-[#22b5d1] py-3 text-lg font-bold text-white transition hover:bg-[#1da3be]">
                    Masuk
                </button>
            </form>

            {{-- 6. Tombol Masuk dengan Google (tampil jika GOOGLE_CLIENT_ID terisi) --}}
            @if (config('services.google.client_id'))
                <p class="my-4 text-center text-sm text-slate-400">— atau —</p>
                <a href="{{ route('google.redirect') }}"
                   class="flex w-full items-center justify-center gap-3 rounded-md border border-slate-300 py-3 font-semibold text-[#0b2d4b] hover:bg-slate-50">
                    <span class="text-xl font-bold text-[#4285F4]">G</span> Masuk dengan Google
                </a>
            @endif

            <p class="mt-5 text-center text-sm text-slate-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-[#22b5d1] hover:underline">Daftar</a>
            </p>
        </div>
    </div>
@endsection
