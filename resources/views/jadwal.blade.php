@extends('layouts.app')

@section('title', 'Jadwal Kuliah')

@section('content')
    <div class="container-fluid">

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Opsi dan Filter Jadwal --}}
        <div class="card mb-4">
            <div class="card-header">Opsi Jadwal</div>
            <div class="card-body">
                <div class="row align-items-center">

                    {{-- PERBAIKAN: Form Generate Jadwal Baru --}}
                    <div class="col-md-6 mb-3 mb-md-0 border-end">
                        <form action="{{ route('jadwal.generate') }}" method="POST">
                            @csrf
                            <h5 class="mb-3">Generate Jadwal Baru</h5>
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="tahun_ajaran" class="form-label">Pilih Tahun Ajaran</label>
                                    <select name="tahun_ajaran" id="tahun_ajaran" class="form-select" required>
                                        <option value="">-- Pilih Tahun --</option>
                                        @foreach ($allTahunAjaran as $tahun)
                                            <option value="{{ $tahun }}">{{ $tahun }}/{{ $tahun + 1 }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="jenis_semester_gen" class="form-label">Pilih Semester</label>
                                    <select name="jenis_semester" id="jenis_semester_gen" class="form-select" required>
                                        <option value="">-- Pilih Semester --</option>
                                        <option value="gasal">Gasal</option>
                                        <option value="genap">Genap</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"
                                        onclick="return confirm('Yakin ingin membuat jadwal baru untuk periode ini? Jadwal lama di periode ini akan diarsipkan.')">
                                        Generate
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <form action="{{ route('jadwal.index') }}" method="GET" id="filter-form">
                            <h5 class="mb-3">Filter Tampilan Jadwal</h5>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="prodi_id" class="form-label">Prodi</label>
                                    <select name="prodi_id" id="prodi_id" class="form-select"
                                        onchange="this.form.submit()">
                                        <option value="">Semua Prodi</option>
                                        @foreach ($allProdi as $prodi)
                                            <option value="{{ $prodi->id }}"
                                                {{ ($prodiId ?? '') == $prodi->id ? 'selected' : '' }}>
                                                {{ $prodi->nama_prodi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="jenis_semester" class="form-label">Semester</label>
                                    <select name="jenis_semester" id="jenis_semester" class="form-select"
                                        onchange="this.form.submit()">
                                        <option value="">Semua Semester</option>
                                        <option value="gasal" {{ ($jenisSemester ?? '') == 'gasal' ? 'selected' : '' }}>
                                            Gasal</option>
                                        <option value="genap" {{ ($jenisSemester ?? '') == 'genap' ? 'selected' : '' }}>
                                            Genap</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('jadwal.index') }}" class="btn btn-secondary w-100">Reset Filter</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tampilan Rekap Jadwal AKTIF --}}
        @if ($jadwal->isNotEmpty())
            <div class="card w-100" style="min-width: 1200px;">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title mb-0">Rekap Jadwal (Aktif Saat Ini)</h3>
                    <a href="{{ route('jadwal.cetak', request()->query()) }}" class="btn btn-sm btn-info"
                        target="_blank">Cetak Jadwal</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 10%;">HARI</th>
                                    <th style="width: 15%;">WAKTU</th>
                                    <th style="width: 25%;">MATA KULIAH</th>
                                    <th style="width: 15%;">KELAS</th>
                                    <th style="width: 20%;">DOSEN</th>
                                    <th style="width: 15%;">RUANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $allJadwals = $jadwal->flatten();
                                    $dayOrder = [
                                        'Senin' => 1,
                                        'Selasa' => 2,
                                        'Rabu' => 3,
                                        'Kamis' => 4,
                                        'Jumat' => 5,
                                        'Sabtu' => 6,
                                    ];
                                    $jadwalGroupedByDay = $allJadwals
                                        ->sortBy(fn($j) => $dayOrder[$j->hari] ?? 99)
                                        ->groupBy('hari');
                                @endphp
                                @forelse ($jadwalGroupedByDay as $hari => $jadwalsOnThisDay)
                                    @foreach ($jadwalsOnThisDay->sortBy('jam_mulai') as $item)
                                        <tr>
                                            @if ($loop->first)
                                                <td rowspan="{{ count($jadwalsOnThisDay) }}">
                                                    <strong>{{ $hari }}</strong></td>
                                            @endif
                                            <td>{{ date('H:i', strtotime($item->jam_mulai)) }} -
                                                {{ date('H:i', strtotime($item->jam_selesai)) }}</td>
                                            <td class="text-start">
                                                {{ $item->penugasan->mataKuliah->nama_mk ?? 'N/A' }} <br>
                                                <small class="text-muted">Semester:
                                                    {{ $item->penugasan->mataKuliah->semester ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $item->penugasan->kelas->nama_kelas ?? 'N/A' }}</td>
                                            <td class="text-start">{{ $item->penugasan->dosen->nama_dosen ?? 'N/A' }}</td>
                                            <td>{{ $item->ruangan->nama_ruangan ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-5">Tidak ada jadwal untuk ditampilkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                <h4>Jadwal Aktif Kosong.</h4>
                <p class="text-muted mb-0">Silakan generate jadwal baru untuk periode yang diinginkan.</p>
            </div>
        @endif

        {{-- Tabel Arsip Jadwal Terdahulu --}}
        <div class="card mt-5">
            <div class="card-header">
                <h3 class="card-title">Arsip Jadwal Terdahulu</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Versi Jadwal</th>
                                <th>Tanggal Diarsipkan</th>
                                <th style="width: 15%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($arsipJadwal as $arsip)
                                <tr>
                                    <td>{{ $arsip->nama_versi }}</td>
                                    <td>{{ \Carbon\Carbon::parse($arsip->created_at)->format('d M Y H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('arsip.jadwal.show', $arsip->id) }}"
                                            class="btn btn-info btn-sm">Lihat</a>
                                        <form action="{{ route('arsip.jadwal.destroy', $arsip->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus arsip ini secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button typeM="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Belum ada arsip jadwal.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    {{ $arsipJadwal->links() }}
                </div>
            </div>
        </div>

    </div>
@endsection
