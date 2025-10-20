<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
            position: fixed;
            top: 0;
            left: 0;
        }

        .sidebar a {
            color: #adb5bd;
            padding: 10px 15px;
            display: block;
            text-decoration: none;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }

        .content-wrapper {
            margin-left: 250px;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar text-white p-3 d-none d-md-block" style="width: 250px;">
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
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                
                {{-- PERBAIKAN: Menambahkan link Profil yang fungsional --}}
                @if (in_array(Auth::user()->role, ['dosen', 'mahasiswa']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">Profil Saya</a>
                    </li>
                @endif


                @if (Auth::user()->role === 'admin')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('prodi.index') }}">Manajemen Prodi sih</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dosen.index') }}">Manajemen Dosen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mata-kuliah.index') }}">Manajemen Mata Kuliah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ruangan.index') }}">Manajemen Ruangan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('kelas.index') }}">Manajemen Kelas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mahasiswa.index') }}">Manajemen Mahasiswa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('penugasan.index') }}">Penugasan Dosen</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('jadwal.index') }}">Generate Jadwal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('jadwal.lihat') }}">Lihat Jadwal Per Kelas</a>
                    </li>
                @endif

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

        <!-- Page Content -->
        <div class="content-wrapper">
            <main class="py-4 px-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

