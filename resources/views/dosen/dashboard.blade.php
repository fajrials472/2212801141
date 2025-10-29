@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
    <div class="container-fluid">
        <div class="card" style="min-width: 1200px;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Jadwal Mengajar Anda</h3>
                <a href="{{ route('dosen.jadwal.cetak', ['tahun_ajaran' => request('tahun_ajaran'), 'jenis_semester' => request('jenis_semester')]) }}"
                    class="btn btn-light btn-sm" target="_blank">
                    <i class="fas fa-print"></i> Cetak Jadwal
                </a>
            </div>
            <div class="card-body">
                {{-- Form filter untuk Tahun Ajaran dan Semester --}}
                <div class="row mb-4 align-items-center">
                    <div class="col-md-8">
                        <form action="{{ route('dosen.dashboard') }}" method="GET" id="filter-form">
                            <div class="row align-items-end">
                                <div class="col-auto">
                                    <label for="tahun_ajaran" class="form-label fw-bold">TAHUN AJARAN</label>
                                    <select name="tahun_ajaran" id="tahun_ajaran" class="form-select"
                                        onchange="this.form.submit()">
                                        <option value="">Pilih Tahun Ajaran</option>
                                        @foreach ($allTahunAjaran as $tahun)
                                            <option value="{{ $tahun }}"
                                                {{ ($tahunAjaran ?? '') == $tahun ? 'selected' : '' }}>
                                                {{ $tahun }}/{{ $tahun + 1 }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <label for="jenis_semester" class="form-label fw-bold">SEMESTER</label>
                                    <select name="jenis_semester" id="jenis_semester" class="form-select"
                                        onchange="this.form.submit()">
                                        <option value="">Semua</option>
                                        <option value="gasal" {{ ($jenisSemester ?? '') == 'gasal' ? 'selected' : '' }}>
                                            Gasal</option>
                                        <option value="genap" {{ ($jenisSemester ?? '') == 'genap' ? 'selected' : '' }}>
                                            Genap</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ route('dosen.dashboard') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <div class="text-end p-2 border rounded bg-light" style="min-width: 300px;">
                            <h5 class="mb-0">{{ $dosen->nama_dosen }}</h5>
                            <p class="text-muted mb-0">NIDN: {{ $dosen->nidn ?? 'Tidak tersedia' }}</p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 10%;">HARI</th>
                                <th style="width: 15%;">JAM</th>
                                <th style="width: 30%;">MATAKULIAH</th>
                                <th style="width: 10%;">SEMESTER</th>
                                <th style="width: 15%;">KELAS</th>
                                <th style="width: 20%;">RUANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Definisikan peta konversi angka hari ke nama hari
                                $mapHari = [
                                    1 => 'Senin',
                                    2 => 'Selasa',
                                    3 => 'Rabu',
                                    4 => 'Kamis',
                                    5 => 'Jumat',
                                    6 => 'Sabtu',
                                    7 => 'Minggu',
                                    99 => 'Minggu', // Jika ada hari dengan kode 99, berikan label N/A
                                ];
                            @endphp
                            @forelse ($jadwalGroupedByDay as $kodeHari => $jadwalsOnThisDay)
                                @foreach ($jadwalsOnThisDay->sortBy('jam_mulai') as $jadwal)
                                    <tr>
                                        @if ($loop->first)
                                            <td rowspan="{{ count($jadwalsOnThisDay) }}">
                                                @php
                                                    // Ambil nama hari dari peta. Jika kode tidak ada, gunakan kode aslinya.
                                                    $namaHari = $mapHari[(int) $kodeHari] ?? $kodeHari;
                                                @endphp
                                                
                                                <strong>{{ $namaHari }}</strong></td>
                                        @endif

                                        <td>
                                            {{ date('H:i', strtotime($jadwal->jam_mulai)) }} -
                                            
                                            {{ date('H:i', strtotime($jadwal->jam_selesai)) }}</td>
                                        <td class="text-start">
                                            {{ $jadwal->penugasan->mataKuliah->nama_mk ?? 'N/A' }}</td>
                                        <td>
                                            {{ $jadwal->penugasan->mataKuliah->semester ?? 'N/A' }}</td>
                                        <td>
                                            {{ $jadwal->penugasan->kelas->nama_kelas ?? 'N/A' }}</td>
                                        <td>
                                            {{ $jadwal->ruangan->nama_ruangan ?? 'N/A' }}</td>
                                        </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="p-5">
                                        <h4>Jadwal tidak ditemukan.</h4>
                                        <p class="text-muted">Tidak ada jadwal yang
                                            cocok dengan filter yang Anda pilih.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
