@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
    <div class="container-fluid">
        <div class="card" style="min-width: 1200px;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Jadwal Mengajar Anda</h3>
                <div>
                    {{-- TOMBOL BARU --}}
                    <button type="button" class="btn btn-warning btn-sm me-2" data-bs-toggle="modal"
                        data-bs-target="#modalGabungKelas">
                        <i class="fas fa-object-group"></i> Ajukan Gabung Kelas
                    </button>

                    <a href="{{ route('dosen.jadwal.cetak') }}" class="btn btn-light btn-sm" target="_blank">
                        <i class="fas fa-print"></i> Cetak Jadwal
                    </a>
                </div>
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
                            @forelse ($jadwalGroupedByDay as $hari => $jadwalsOnThisDay)
                                @foreach ($jadwalsOnThisDay->sortBy('jam_mulai') as $jadwal)
                                    <tr>
                                        @if ($loop->first)
                                            <td rowspan="{{ count($jadwalsOnThisDay) }}">
                                                <strong>{{ $hari }}</strong>
                                            </td>
                                        @endif

                                        <td>
                                            {{ date('H:i', strtotime($jadwal->jam_mulai)) }} -
                                            {{ date('H:i', strtotime($jadwal->jam_selesai)) }}
                                        </td>
                                        <td class="text-start">
                                            {{ $jadwal->penugasan->mataKuliah->nama_mk ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $jadwal->penugasan->mataKuliah->semester ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $jadwal->penugasan->kelas->nama_kelas ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $jadwal->ruangan->nama_ruangan ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="p-5">
                                        <h4>Jadwal tidak ditemukan.</h4>
                                        <p class="text-muted">Tidak ada jadwal yang cocok dengan filter yang Anda pilih.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL AJUKAN GABUNG KELAS --}}
    <div class="modal fade" id="modalGabungKelas" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-white">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Pengajuan Penggabungan Kelas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-dark">
                    <div class="alert alert-warning border-warning">
                        <small>
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Petunjuk:</strong>
                            1. Centang (<i class="far fa-check-square"></i>) kelas-kelas yang ingin digabung.<br>
                            2. Pilih salah satu <strong>Jadwal Acuan</strong> (<i class="far fa-dot-circle"></i>) yang akan
                            digunakan untuk semua kelas tersebut.
                        </small>
                    </div>

                    @if (isset($kandidatGabung) && $kandidatGabung->isNotEmpty())
                        @foreach ($kandidatGabung as $mkId => $items)
                            @php $namaMk = $items->first()->nama_mk; @endphp

                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-light fw-bold">
                                    <i class="fas fa-book"></i> Mata Kuliah: {{ $namaMk }}
                                </div>
                                <div class="card-body p-0">
                                    <form action="{{ route('dosen.request.gabung') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="mata_kuliah_id" value="{{ $mkId }}">

                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0 align-middle">
                                                <thead class="table-dark small">
                                                    <tr>
                                                        <th class="text-center" width="10%">Gabung?</th>
                                                        <th width="20%">Kelas</th>
                                                        <th width="50%">Jadwal Saat Ini</th>
                                                        <th class="text-center" width="20%">Pilih Jadwal Ini</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($items as $item)
                                                        <tr>
                                                            {{-- CHECKBOX: Pilih Kelas yang mau digabung --}}
                                                            <td class="text-center">
                                                                <input class="form-check-input border-secondary"
                                                                    type="checkbox" name="kelas_ids[]"
                                                                    value="{{ $item->kelas_id }}"
                                                                    style="transform: scale(1.3); cursor: pointer;">
                                                            </td>

                                                            <td class="fw-bold">{{ $item->nama_kelas }}</td>

                                                            {{-- TAMPILAN JADWAL ASLI --}}
                                                            <td class="text-muted small">
                                                                <i class="far fa-calendar-alt"></i>
                                                                {{ $item->hari ?? '-' }} <br>
                                                                <i class="far fa-clock"></i>
                                                                {{ isset($item->jam_mulai) ? date('H:i', strtotime($item->jam_mulai)) : '--' }}
                                                                -
                                                                {{ isset($item->jam_selesai) ? date('H:i', strtotime($item->jam_selesai)) : '--' }}
                                                                <br>
                                                                <i class="fas fa-door-open"></i>
                                                                {{ $item->nama_ruangan ?? 'Online' }}
                                                            </td>

                                                            {{-- RADIO BUTTON: Pilih Jadwal Mana yang Dipakai --}}
                                                            {{-- GANTI BAGIAN RADIO BUTTON INI (Kira-kira baris 191 - 198) --}}
                                                            <td class="text-center bg-light">
                                                                <div class="form-check d-flex justify-content-center">
                                                                    {{-- PERBAIKAN: Ganti $item->id menjadi $item->kelas_id --}}
                                                                    <input class="form-check-input" type="radio"
                                                                        name="jadwal_utama_id"
                                                                        value="{{ $item->kelas_id }}" required
                                                                        style="transform: scale(1.3); cursor: pointer;">
                                                                </div>
                                                                <small class="text-muted d-block mt-1">Pakai Jadwal
                                                                    Ini</small>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="p-3 text-end bg-light border-top">
                                            <button type="submit" class="btn btn-success fw-bold">
                                                <i class="fas fa-link"></i> Proses Gabung Kelas
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <img src="https://img.icons8.com/ios/100/cccccc/nothing-found.png" alt="Empty"
                                style="width: 80px; opacity: 0.5;">
                            <h4 class="text-muted mt-3">Tidak ada kelas yang memenuhi syarat.</h4>
                            <p class="text-muted">Anda harus memiliki minimal 2 kelas pada mata kuliah yang sama untuk
                                menggabungkannya.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    
@endsection
