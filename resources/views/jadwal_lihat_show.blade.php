@extends('layouts.app')

@section('title', 'Jadwal ' . $kelas->nama_kelas)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Jadwal Kuliah Kelas: {{ $kelas->nama_kelas }}</h1>
        <a href="{{ route('jadwal.lihat') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <!-- Formulir Filter -->
    <form action="{{ route('jadwal.lihat.kelas', $kelas->id) }}" method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" name="tahun_ajaran" class="form-control" placeholder="Tahun Ajaran (contoh: 2024-2025)" value="{{ request('tahun_ajaran') }}">
            </div>
            <div class="col-md-4">
                <select name="jenis_semester" class="form-control">
                    <option value="">Pilih Semester</option>
                    <option value="ganjil" {{ request('jenis_semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="genap" {{ request('jenis_semester') == 'genap' ? 'selected' : '' }}>Genap</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </div>
    </form>
    
    @if (empty($jadwalGrid))
        <div class="alert alert-info">Tidak ada jadwal yang tersedia untuk kelas ini.</div>
    @else
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Waktu</th>
                    <th>Mata Kuliah</th>
                    <th>Dosen</th>
                    <th>Ruangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $hariKuliah = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                @endphp
                @foreach ($hariKuliah as $hari)
                    @if (isset($jadwalGrid[$hari]))
                        @foreach ($jadwalGrid[$hari] as $jadwal)
                            <tr>
                                <td>{{ $hari }}</td>
                                <td>{{ date('H:i', strtotime($jadwal->jam_mulai)) }} - {{ date('H:i', strtotime($jadwal->jam_selesai)) }}</td>
                                <td>{{ $jadwal->mataKuliah->nama_mk }}</td>
                                <td>{{ $jadwal->dosen->nama_dosen }}</td>
                                <td>{{ $jadwal->ruangan->nama_ruangan }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
