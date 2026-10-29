{{-- Banner utama. Variabel opsional: $eyebrow (teks kecil di atas judul). --}}
@php
    $heroUser = auth()->user();
    $heroBackOffice = (bool) ($heroUser && $heroUser->role?->isBackOffice() && ! $heroUser->isAdmin());
    $ctaHref = $heroBackOffice ? '/admin' : route('bookings.create');
    $ctaLabel = $heroBackOffice ? 'Buka Panel Admin' : 'Pesan Sekarang';
    $eyebrow = $eyebrow ?? 'Selamat datang di';
@endphp
{{-- Foto latar: public/images/hero-carwash.jpg (ganti file ini untuk mengubah foto) --}}
<section class="relative overflow-hidden bg-brand-dark bg-cover bg-[position:center_35%] text-white"
         style="background-image:url('{{ asset('images/hero-carwash.jpg') }}')">
    {{-- Lapisan biru gelap agar teks terbaca; makin transparan ke kanan supaya foto terlihat --}}
    <div class="absolute inset-0 bg-brand-dark/80 md:bg-transparent md:bg-gradient-to-r md:from-brand-dark/95 md:via-brand-dark/50 md:to-transparent"></div>

    <div class="relative mx-auto flex min-h-[420px] max-w-6xl items-center px-4 py-12 md:py-20">
        <div class="max-w-lg">
            <p class="text-sm font-semibold uppercase tracking-widest text-sky-100">{{ $eyebrow }}</p>
            <h1 class="mt-2 text-4xl font-extrabold leading-tight sm:text-5xl">
                Bara Cuci <span class="text-brand-light">Kinclong</span>
            </h1>
            <p class="mt-4 max-w-md text-lg text-sky-50">
                Solusi terbaik untuk kendaraan bersih, sehat, dan selalu terlihat prima!
            </p>

            <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-4">
                @foreach ([['shield', 'Hasil', 'Maksimal'], ['star', 'Harga', 'Terjangkau'], ['clock', 'Proses Cepat', '& Profesional']] as [$icon, $l1, $l2])
                    <li class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white/15">
                            <x-ikon :name="$icon" class="h-6 w-6" />
                        </span>
                        <span class="text-sm font-semibold leading-tight">{{ $l1 }}<br>{{ $l2 }}</span>
                    </li>
                @endforeach
            </ul>

            <a href="{{ $ctaHref }}"
               class="mt-8 inline-flex items-center gap-2 rounded-full bg-sky-400 px-7 py-3 font-bold text-white shadow-lg shadow-brand-dark/30 transition hover:bg-sky-300">
                {{ $ctaLabel }} <x-ikon name="arrow" class="h-5 w-5" />
            </a>
        </div>
    </div>
</section>
