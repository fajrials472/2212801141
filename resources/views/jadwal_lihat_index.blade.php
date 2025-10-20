@extends('layouts.app')

@section('title', 'Lihat Jadwal Per Kelas')

@section('content')
    <h1 class="mb-4">Lihat Jadwal Per Kelas</h1>

    <form action="{{ route('jadwal.lihat') }}" method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <select name="semester" id="semester" class="form-control" onchange="this.form.submit()">
                    <option value="">Pilih Semester</option>
                    <option value="1" {{ $semester == 1 ? 'selected' : '' }}>1</option>
                    <option value="2" {{ $semester == 2 ? 'selected' : '' }}>2</option>
                    <option value="3" {{ $semester == 3 ? 'selected' : '' }}>3</option>
                    <option value="4" {{ $semester == 4 ? 'selected' : '' }}>4</option>
                    <option value="5" {{ $semester == 5 ? 'selected' : '' }}>5</option>
                    <option value="6" {{ $semester == 6 ? 'selected' : '' }}>6</option>
                    <option value="7" {{ $semester == 7 ? 'selected' : '' }}>7</option>
                    <option value="8" {{ $semester == 8 ? 'selected' : '' }}>8</option>
                </select>
            </div>
            <div class="col-md-4">
                <a href="{{ route('jadwal.lihat') }}" class="btn btn-secondary">Reset Filter</a>
            </div>
        </div>
    </form>

    @if (empty($prodi))
        <div class="alert alert-info">Tidak ada prodi yang ditemukan.</div>
    @else
        @foreach ($prodi as $p)
            <div class="card mb-3">
                <div class="card-header">
                    <h4>{{ $p->nama_prodi }}</h4>
                </div>
                <div class="card-body">
                    @if ($p->kelas->isEmpty())
                        <p>Tidak ada kelas untuk prodi ini di semester yang dipilih.</p>
                    @else
                        @foreach ($p->kelas as $kelas)
                            <a href="{{ route('jadwal.lihat.kelas', $kelas->id) }}" class="btn btn-outline-primary mb-2">
                                {{ $kelas->nama_kelas }}
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    @endif
@endsection
