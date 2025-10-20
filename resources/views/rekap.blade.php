@extends('layouts.app')

@section('title', 'Rekapitulasi Mata Kuliah')

@section('content')
    <h1 class="mb-4">Rekap Mata Kuliah</h1>

    <form action="{{ route('rekap.index') }}" method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-2">
                <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran" class="form-control" value="{{ $tahunAjaran ?? '' }}">
            </div>
            <div class="col-md-2">
                <label for="jenis_semester" class="form-label">Semester</label>
                <select name="jenis_semester" id="jenis_semester" class="form-control">
                    <option value="">Pilih Semester</option>
                    <option value="genap" {{ $jenisSemester == 'genap' ? 'selected' : '' }}>Genap</option>
                    <option value="ganjil" {{ $jenisSemester == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="prodi_id" class="form-label">Prodi</label>
                <select name="prodi_id" id="prodi_id" class="form-control">
                    <option value="">Pilih Prodi</option>
                    @foreach ($allProdi as $prodi)
                        <option value="{{ $prodi->id }}" {{ $prodiId == $prodi->id ? 'selected' : '' }}>
                            {{ $prodi->nama_prodi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="kelas_id" class="form-label">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-control">
                    <option value="">Pilih Kelas</option>
                    @foreach ($allKelas as $kelas)
                        <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="dosen_id" class="form-label">Dosen</label>
                <select name="dosen_id" id="dosen_id" class="form-control">
                    <option value="">Pilih Dosen</option>
                    @foreach ($allDosen as $dosen)
                        <option value="{{ $dosen->id }}" {{ $dosenId == $dosen->id ? 'selected' : '' }}>
                            {{ $dosen->nama_dosen }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Tampilkan</button>
                <a href="{{ route('rekap.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

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
            @if ($rekapData->isEmpty())
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data rekap.</td>
                </tr>
            @else
                @foreach ($rekapData as $data)
                    <tr>
                        <td>{{ $data->hari }}</td>
                        <td>{{ date('H:i', strtotime($data->jam_mulai)) }} - {{ date('H:i', strtotime($data->jam_selesai)) }}</td>
                        <td>{{ $data->mataKuliah->nama_mk }}</td>
                        <td>{{ $data->dosen->nama_dosen }}</td>
                        <td>{{ $data->ruangan->nama_ruangan }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
@endsection
