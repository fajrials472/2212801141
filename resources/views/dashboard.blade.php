@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h1 class="mb-4">Dashboard Admin</h1>

    {{-- Kartu Statistik --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info p-3 rounded text-white mb-3" style="position: relative;">
                <div class="inner">
                    <h3>{{ $totalDosen }}</h3>
                    <p>Total Dosen</p>
                </div>
                <div class="icon fs-1 opacity-25" style="position: absolute; top: 15px; right: 15px;">
                    <i class="fas fa-user-tie"></i>
                </div>
                <a href="{{ route('dosen.index') }}" class="small-box-footer text-white text-decoration-none">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success p-3 rounded text-white mb-3" style="position: relative;">
                <div class="inner">
                    <h3>{{ $totalMahasiswa }}</h3>
                    <p>Total Mahasiswa</p>
                </div>
                <div class="icon fs-1 opacity-25" style="position: absolute; top: 15px; right: 15px;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <a href="{{ route('mahasiswa.index') }}" class="small-box-footer text-white text-decoration-none">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning p-3 rounded text-dark mb-3" style="position: relative;">
                <div class="inner">
                    <h3>{{ $totalMataKuliah }}</h3>
                    <p>Total Mata Kuliah</p>
                </div>
                <div class="icon fs-1 opacity-25" style="position: absolute; top: 15px; right: 15px;">
                    <i class="fas fa-book"></i>
                </div>
                <a href="{{ route('mata-kuliah.index') }}" class="small-box-footer text-dark text-decoration-none">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger p-3 rounded text-white mb-3" style="position: relative;">
                <div class="inner">
                    <h3>{{ $totalKelas }}</h3>
                    <p>Total Kelas</p>
                </div>
                <div class="icon fs-1 opacity-25" style="position: absolute; top: 15px; right: 15px;">
                    <i class="fas fa-school"></i>
                </div>
                <a href="{{ route('kelas.index') }}" class="small-box-footer text-white text-decoration-none">
                    Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Fitur Download Data --}}
    <div class="row mt-4">
        {{-- Download Data Mahasiswa --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-download me-2"></i>Download Data Mahasiswa</h3>
                </div>
                {{-- PERBAIKAN: Menambahkan kurung kurawal {{ }} --}}
                <form action="{{ route('mahasiswa.download') }}" method="GET" target="_blank">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="kelas_id" class="form-label">Berdasarkan Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-select">
                                <option value="semua">-- Semua Kelas --</option>
                                @foreach($allKelas as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="angkatan" class="form-label">Berdasarkan Angkatan</label>
                            <select name="angkatan" id="angkatan" class="form-select">
                                <option value="semua">-- Semua Angkatan --</option>
                                 @foreach($allAngkatan as $angkatan)
                                <option value="{{ $angkatan }}">{{ $angkatan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Download PDF</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Download Data Dosen --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-download me-2"></i>Download Data Dosen</h3>
                </div>
                {{-- PERBAIKAN: Menambahkan kurung kurawal {{ }} --}}
                <form action="{{ route('dosen.download') }}" method="GET" target="_blank">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="prodi_id_dosen" class="form-label">Berdasarkan Prodi</label>
                            <select name="prodi_id" id="prodi_id_dosen" class="form-select">
                                <option value="semua">-- Semua Prodi --</option>
                                @foreach($allProdi as $prodi)
                                <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Download PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection