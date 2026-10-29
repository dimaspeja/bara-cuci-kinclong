{{--
    SATU-SATUNYA tempat logo didefinisikan. Dipakai di navbar, footer, dan halaman login.
    Ganti logo: timpa public/images/logo.png (dan favicon.png) dengan nama yang sama.
    Gambar sebaiknya PNG transparan, persegi, minimal 512 px.

    variant: 'nav' (navbar, latar putih) | 'footer' (latar biru tua) | 'login' (besar, di kartu login)
--}}
@props(['variant' => 'nav'])
@if ($variant === 'login')
    <img src="{{ asset('images/logo.png') }}" alt="Logo Bara Cuci Kinclong" class="h-28 w-28">
@else
    @php
        $dark = $variant === 'footer';
    @endphp
    <span class="flex items-center gap-3">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Bara Cuci Kinclong" class="shrink-0 {{ $dark ? 'h-14 w-14' : 'h-12 w-12' }}">
        <span class="leading-tight">
            <span class="block {{ $dark ? 'text-base font-bold text-white' : 'text-lg font-extrabold text-brand-dark' }}">Bara Cuci Kinclong</span>
            <span class="block text-xs {{ $dark ? 'text-slate-300' : 'text-slate-500' }}">Bersih • Kinclong • Terpercaya</span>
        </span>
    </span>
@endif
