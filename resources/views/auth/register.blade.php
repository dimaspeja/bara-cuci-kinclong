@extends('layouts.app')

@section('title', 'Daftar · Bara Cuci Kinclong')

@section('content')
    <div class="mx-auto max-w-sm rounded-xl border border-slate-200 bg-white p-6">
        <h1 class="text-xl font-bold">Buat Akun</h1>

        <form method="POST" action="{{ route('register.store') }}" class="mt-5 space-y-4">
            @csrf
            @foreach ([
                ['name', 'Nama lengkap', 'text', true],
                ['email', 'Email', 'email', true],
                ['phone', 'Nomor telepon / WhatsApp (opsional)', 'tel', false],
                ['password', 'Kata sandi (min. 8 karakter)', 'password', true],
                ['password_confirmation', 'Ulangi kata sandi', 'password', true],
            ] as [$field, $label, $type, $required])
                <div>
                    <label for="{{ $field }}" class="text-sm font-medium">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}"
                           @if ($type !== 'password') value="{{ old($field) }}" @endif
                           @if ($required) required @endif
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 focus:border-sky-500 focus:outline-none">
                </div>
            @endforeach
            <button class="w-full rounded-md bg-sky-600 py-2 font-medium text-white hover:bg-sky-700">Daftar</button>
        </form>

        @if (config('services.google.client_id'))
            <div class="my-5 flex items-center gap-3 text-xs text-slate-400">
                <span class="h-px flex-1 bg-slate-200"></span> atau <span class="h-px flex-1 bg-slate-200"></span>
            </div>
            <a href="{{ route('google.redirect') }}"
               class="flex w-full items-center justify-center gap-2 rounded-md border border-slate-300 py-2 text-sm font-medium hover:bg-slate-50">
                <span class="font-bold text-sky-600">G</span> Daftar dengan Google
            </a>
        @endif

        <p class="mt-5 text-center text-sm text-slate-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-sky-600 hover:underline">Masuk</a>
        </p>
    </div>
@endsection
