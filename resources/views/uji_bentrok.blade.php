@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-microscope"></i> Simulasi & Bukti Uji Bentrok</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('uji.cek') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label>Pilih Dosen</label>
                            <select name="dosen_id" class="form-select" required>
                                @foreach ($dosen as $d)
                                    <option value="{{ $d->id }}"
                                        {{ isset($input) && $input['dosen_id'] == $d->id ? 'selected' : '' }}>
                                        {{ $d->nama_dosen }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Hari</label>
                            <select name="hari" class="form-select" required>
                                <option value="Senin" {{ isset($input) && $input['hari'] == 'Senin' ? 'selected' : '' }}>
                                    Senin</option>
                                <option value="Selasa"
                                    {{ isset($input) && $input['hari'] == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                                <option value="Rabu" {{ isset($input) && $input['hari'] == 'Rabu' ? 'selected' : '' }}>
                                    Rabu</option>
                                <option value="Kamis" {{ isset($input) && $input['hari'] == 'Kamis' ? 'selected' : '' }}>
                                    Kamis</option>
                                <option value="Jumat" {{ isset($input) && $input['hari'] == 'Jumat' ? 'selected' : '' }}>
                                    Jumat</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control"
                                value="{{ $input['jam_mulai'] ?? '' }}" required>
                        </div>
                        <div class="col-md-2">
                            <label>Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-control"
                                value="{{ $input['jam_selesai'] ?? '' }}" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-warning w-100 fw-bold">
                                <i class="fas fa-search"></i> Lakukan Tes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if (isset($hasilAnalisa))
            <div class="card shadow-lg">
                <div class="card-header fw-bold bg-dark text-white">
                    Hasil Penelusuran (Trace Table)
                </div>
                <div class="card-body">
                    {{-- KESIMPULAN UTAMA --}}
                    <div class="alert {{ $statusAkhir == 'BENTROK' ? 'alert-danger' : 'alert-success' }} text-center mb-4">
                        <h3>KESIMPULAN: <strong>{{ $statusAkhir }}</strong></h3>
                        <p>
                            Input yang diuji:
                            <strong>{{ $input['hari'] }}, {{ $input['jam_mulai'] }} -
                                {{ $input['jam_selesai'] }}</strong>
                        </p>
                    </div>

                    {{-- TABEL BUKTI MANUAL --}}
                    <h5 class="fw-bold mb-3">Tabel Pembuktian Logika:</h5>
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-secondary text-center">
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Jadwal Eksisting di Database</th>
                                <th width="45%">Analisa Sistem (Logika)</th>
                                <th width="20%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hasilAnalisa as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>

                                    {{-- KOLOM 1: DATA DI DB --}}
                                    <td>
                                        <i class="fas fa-database text-muted"></i>
                                        {{ $row['data_db'] }}
                                    </td>

                                    {{-- KOLOM 2: ANALISA --}}
                                    <td>
                                        <small class="text-muted">
                                            <em>"Apakah {{ $input['jam_mulai'] }}-{{ $input['jam_selesai'] }} bertabrakan
                                                dengan jadwal ini?"</em>
                                        </small><br>
                                        <strong>Jawab:</strong> {{ $row['analisa'] }}
                                    </td>

                                    {{-- KOLOM 3: KEPUTUSAN PER BARIS --}}
                                    <td class="text-center fw-bold">
                                        @if ($row['status'] == 'BENTROK')
                                            <span class="badge bg-danger p-2">BENTROK <i
                                                    class="fas fa-times-circle"></i></span>
                                        @else
                                            <span class="badge bg-success p-2">AMAN <i
                                                    class="fas fa-check-circle"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada data untuk dianalisa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
