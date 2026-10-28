{{-- Banner utama. Variabel opsional: $eyebrow (teks kecil di atas judul). --}}
@php
    $heroUser = auth()->user();
    $heroBackOffice = (bool) ($heroUser && $heroUser->role?->isBackOffice() && ! $heroUser->isAdmin());
    $ctaHref = $heroBackOffice ? '/admin' : route('bookings.create');
    $ctaLabel = $heroBackOffice ? 'Buka Panel Admin' : 'Pesan Sekarang';
    $eyebrow = $eyebrow ?? 'Selamat datang di';
@endphp
<section class="relative overflow-hidden bg-gradient-to-r from-brand-dark via-brand to-[#2a86f0] text-white">
    {{-- gelembung dekoratif --}}
    <span class="absolute -left-10 top-10 h-40 w-40 rounded-full bg-white/10"></span>
    <span class="absolute left-1/3 top-4 h-6 w-6 rounded-full bg-white/20"></span>
    <span class="absolute bottom-6 left-1/2 h-10 w-10 rounded-full bg-white/10"></span>

    <div class="relative mx-auto grid max-w-6xl items-center gap-6 px-4 py-12 md:grid-cols-2 md:py-16">
        <div>
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

        {{-- ilustrasi mobil (SVG buatan sendiri) --}}
        <div class="relative">
            <svg viewBox="0 0 520 260" class="mx-auto w-full max-w-xl drop-shadow-2xl" role="img" aria-label="Mobil sedang dicuci">
                <ellipse cx="260" cy="232" rx="225" ry="13" fill="#001a4d" opacity=".35"/>
                <path d="M34 188c0-22 20-34 54-40l58-62c10-10 22-14 38-14h130c18 0 32 6 44 18l50 54c34 6 60 16 62 40v18H34z" fill="#1d6fe0"/>
                <path d="M34 188c0-22 20-34 54-40l58-62c10-10 22-14 38-14h130c18 0 32 6 44 18l50 54" fill="none" stroke="#7cc4ff" stroke-width="3" opacity=".7"/>
                <path d="M166 100h72v46H116z" fill="#d6ebff"/>
                <path d="M250 100h74c8 0 14 3 20 9l30 37H250z" fill="#bfe0ff"/>
                <path d="M60 170h400" stroke="#0b3e91" stroke-width="3" opacity=".45"/>
                <rect x="440" y="160" width="34" height="12" rx="6" fill="#fff6c9"/>
                <circle cx="136" cy="206" r="36" fill="#0a1f44"/><circle cx="136" cy="206" r="17" fill="#cbd5e1"/><circle cx="136" cy="206" r="6" fill="#64748b"/>
                <circle cx="396" cy="206" r="36" fill="#0a1f44"/><circle cx="396" cy="206" r="17" fill="#cbd5e1"/><circle cx="396" cy="206" r="6" fill="#64748b"/>
                {{-- busa dan gelembung --}}
                <g fill="#fff" opacity=".9">
                    <circle cx="190" cy="84" r="9"/><circle cx="214" cy="72" r="6"/><circle cx="300" cy="78" r="10"/>
                    <circle cx="330" cy="92" r="6"/><circle cx="260" cy="64" r="5"/><circle cx="110" cy="132" r="7"/>
                    <circle cx="420" cy="136" r="8"/><circle cx="360" cy="70" r="5"/>
                </g>
                <g fill="#fff" opacity=".45">
                    <circle cx="70" cy="70" r="16"/><circle cx="480" cy="90" r="12"/><circle cx="452" cy="52" r="7"/>
                </g>
            </svg>
            <p class="absolute bottom-2 right-2 hidden -rotate-6 text-right text-lg font-semibold italic leading-tight text-white sm:block">
                Kendaraan bersih,<br>hati lebih baik! ♡
            </p>
        </div>
    </div>
</section>
