<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bara Cuci Kinclong')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
@php($authUser = auth()->user())
<header class="border-b border-slate-200 bg-white">
    <nav class="mx-auto flex max-w-5xl flex-wrap items-center gap-x-5 gap-y-2 px-4 py-3 text-sm">
        <a href="{{ route('landing') }}" class="mr-2 text-lg font-bold text-sky-600">Bara Cuci Kinclong</a>

        <a href="{{ route('services') }}" class="hover:text-sky-600">Layanan</a>

        {{-- FASE 2: + Layanan. Menu lain ditambah per fase. --}}
        @if ($authUser && ! $authUser->role?->isBackOffice())
            <a href="{{ route('home') }}" class="hover:text-sky-600">Beranda</a>
        @endif

        @if ($authUser && $authUser->role?->isBackOffice())
            <a href="/admin" class="font-medium text-sky-600">Panel Admin</a>
        @endif

        <div class="ml-auto flex items-center gap-3">
            @auth
                <span class="text-slate-500">{{ $authUser->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-md border border-slate-300 px-3 py-1 hover:bg-slate-100">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:text-sky-600">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-md bg-sky-600 px-3 py-1 text-white hover:bg-sky-700">Daftar</a>
            @endauth
        </div>
    </nav>
</header>

<main class="mx-auto max-w-5xl px-4 py-8">
    @if (session('status'))
        <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="py-8 text-center text-xs text-slate-400">
    Bara Cuci Kinclong · Bersih · Cepat · Terpercaya
</footer>
</body>
</html>
