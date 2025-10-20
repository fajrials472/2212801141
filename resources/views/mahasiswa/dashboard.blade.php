@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
    <div class="container-fluid">

        @if ($mahasiswaKelas)

            <div class="card" style="min-width: 1200px;">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Jadwal Kuliah Anda</h3>
                    <a href="{{ route('mahasiswa.jadwal.cetak') }}" class="btn btn-light btn-sm" target="_blank">
                        <i class="fas fa-print"></i> Cetak Jadwal
                    </a>
                </div>
                <div class="card-body">
                    {{-- PERBAIKAN: Form filter baru seperti di dashboard Dosen --}}
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-8">
                            <form action="{{ route('mahasiswa.dashboard') }}" method="GET">
                                <div class="row align-items-end">
                                    <div class="col-auto">
                                        <label class="form-label fw-bold">TAHUN AJARAN</label>
                                        {{-- Ditampilkan sebagai teks, tidak bisa diubah --}}
                                        <p class="fs-5">
                                            {{ $mahasiswaKelas->angkatan }}/{{ $mahasiswaKelas->angkatan + 1 }}</p>
                                    </div>
                                    <div class="col-auto">
                                        <label for="jenis_semester" class="form-label fw-bold">SEMESTER</label>
                                        <select name="jenis_semester" id="jenis_semester" class="form-select"
                                            onchange="this.form.submit()">
                                            <option value="">Semua</option>
                                            <option value="gasal"
                                                {{ ($jenisSemester ?? '') == 'gasal' ? 'selected' : '' }}>Gasal</option>
                                            <option value="genap"
                                                {{ ($jenisSemester ?? '') == 'genap' ? 'selected' : '' }}>Genap</option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-secondary">Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4 d-flex justify-content-end">
                            <div class="text-end p-2 border rounded bg-light" style="min-width: 300px;">
                                <h5 class="mb-0">{{ $user->name }}</h5>
                                <p class="text-muted mb-0">NIM: {{ $user->nim ?? 'Tidak tersedia' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Jadwal --}}
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 10%;">HARI</th>
                                    <th style="width: 15%;">JAM</th>
                                    <th style="width: 30%;">MATAKULIAH</th>
                                    <th style="width: 10%;">SEMESTER</th>
                                    <th style="width: 20%;">DOSEN</th>
                                    <th style="width: 15%;">RUANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jadwalGroupedByDay as $hari => $jadwalsOnThisDay)
                                    @foreach ($jadwalsOnThisDay->sortBy('jam_mulai') as $jadwal)
                                        <tr>
                                            @if ($loop->first)
                                                <td rowspan="{{ count($jadwalsOnThisDay) }}">
                                                    <strong>{{ $hari }}</strong>
                                                </td>
                                            @endif

                                            <td>{{ date('H:i', strtotime($jadwal->jam_mulai)) }} -
                                                {{ date('H:i', strtotime($jadwal->jam_selesai)) }}</td>
                                            <td class="text-start">{{ $jadwal->penugasan->mataKuliah->nama_mk ?? 'N/A' }}
                                            </td>
                                            <td>{{ $jadwal->penugasan->mataKuliah->semester ?? 'N/A' }}</td>
                                            <td class="text-start">{{ $jadwal->penugasan->dosen->nama_dosen ?? 'N/A' }}
                                            </td>
                                            <td>{{ $jadwal->ruangan->nama_ruangan ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-5">
                                            <h4>Jadwal tidak ditemukan.</h4>
                                            <p class="text-muted">Tidak ada jadwal yang tersedia untuk semester yang Anda
                                                pilih.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <h4 class="alert-heading">Anda Belum Terdaftar di Kelas</h4>
                <p>Saat ini akun Anda belum terhubung dengan kelas manapun. Jadwal kuliah akan tampil di sini setelah
                    administrator menempatkan Anda di sebuah kelas.</p>
                <hr>
                <p class="mb-0">Silakan hubungi bagian administrasi jika Anda merasa ini adalah sebuah kesalahan.</p>
            </div>

        @endif

    </div>
@endsection
