{{--
    Logo Resmi Badan Pusat Statistik (vektor SVG, beresolusi tak terbatas).
    Berkas lokal public/img/bps-logo.svg — sumber: Wikimedia Commons (public
    domain Indonesia: UU 28/2014 Pasal 43). Tanpa CDN; aman untuk intranet.

    Pemakaian: @include('partials.bps-logo', ['class' => 'h-10 w-auto'])
--}}
@props(['class' => 'h-9 w-auto', 'alt' => 'Logo Resmi Badan Pusat Statistik'])
<img src="{{ asset('img/bps-logo.svg') }}" alt="{{ $alt }}" class="{{ $class }}" width="48" height="37" loading="eager" decoding="async">
