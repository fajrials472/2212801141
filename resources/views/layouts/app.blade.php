<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard - @yield('title')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- 1. LOAD TAILWIND (VITE) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- 2. LOAD BOOTSTRAP --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    {{-- 3. CSS PATCH (PERBAIKAN KHUSUS KONFLIK TAILWIND VS BOOTSTRAP) --}}
    <style>
        /* Paksa Checkbox agar terlihat (mengalahkan reset Tailwind) */
        .form-check-input {
            appearance: auto !important;
            /* Kembalikan bentuk asli checkbox browser */
            -webkit-appearance: auto !important;
            width: 1.2em !important;
            height: 1.2em !important;
            margin-right: 0.5em !important;
            border: 1px solid #6c757d !important;
            /* Beri garis tepi agar terlihat */
            background-color: #fff !important;
        }

        /* Pastikan teks di dalam modal/accordion berwarna hitam */
        .modal-body,
        .accordion-button,
        .list-group-item,
        .form-check-label {
            color: #000 !important;
            font-size: 1rem !important;
        }

        /* Perbaikan konflik class .collapse antara Tailwind dan Bootstrap */
        .accordion-collapse.collapse:not(.show) {
            display: none !important;
        }

        .accordion-collapse.collapse.show {
            display: block !important;
        }

        /* Pastikan background accordion putih, bukan transparan */
        .accordion-item {
            background-color: #fff !important;
            border: 1px solid #dee2e6 !important;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="d-flex">
        <div class="sidebar text-white p-3 d-none d-md-block">
            @auth
                <h4 class="mb-4">
                    @if (Auth::user()->role === 'admin')
                        Admin Panel
                    @elseif (Auth::user()->role === 'dosen')
                        Dosen Panel
                    @else
                        Mahasiswa Panel
                    @endif
                </h4>
                <p class="text-white-50">Login sebagai: {{ Auth::user()->name }}</p>
                <hr class="text-white">
            @endauth
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">Dashboard</a>
                </li>

                {{-- Link Profil untuk Dosen & Mahasiswa --}}
                @if (in_array(Auth::user()->role, ['dosen', 'mahasiswa']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"
                            href="{{ route('profile.edit') }}">Profil Saya</a>
                    </li>
                @endif

                {{-- Link Menu Admin --}}
                @if (Auth::user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('prodi.index') ? 'active' : '' }}"
                            href="{{ route('prodi.index') }}">Manajemen Prodi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dosen.index') ? 'active' : '' }}"
                            href="{{ route('dosen.index') }}">Manajemen Dosen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('mata-kuliah.index') ? 'active' : '' }}"
                            href="{{ route('mata-kuliah.index') }}">Manajemen Mata Kuliah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('ruangan.index') ? 'active' : '' }}"
                            href="{{ route('ruangan.index') }}">Manajemen Ruangan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kelas.index') ? 'active' : '' }}"
                            href="{{ route('kelas.index') }}">Manajemen Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('mahasiswa.index') ? 'active' : '' }}"
                            href="{{ route('mahasiswa.index') }}">Manajemen Mahasiswa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('penugasan.index') ? 'active' : '' }}"
                            href="{{ route('penugasan.index') }}">Penugasan Dosen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('jadwal.index') ? 'active' : '' }}"
                            href="{{ route('jadwal.index') }}">Generate Jadwal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('jadwal.lihat') ? 'active' : '' }}"
                            href="{{ route('jadwal.lihat') }}">Lihat Jadwal Per Kelas</a>
                    </li>

                    {{-- MENU BARU: Disamakan formatnya dengan yang lain --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('uji.*') ? 'active' : '' }}"
                            href="{{ route('uji.index') }}">
                            Uji Bentrok & Simulasi
                        </a>
                    </li>
                @endif

                {{-- Tombol Logout --}}
                @auth
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="nav-link" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Logout
                            </a>
                        </form>
                    </li>
                @endauth
            </ul>
        </div>

        <div class="content-wrapper">
            <main class="py-4 px-4">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
