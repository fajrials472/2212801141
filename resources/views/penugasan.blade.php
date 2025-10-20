@extends('layouts.app')

@section('title', 'Penugasan Dosen')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Penugasan Dosen dan Kelas</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    {{-- Form untuk Filter --}}
    <div class="card mb-4">
        <div class="card-header">Filter Data</div>
        <div class="card-body">
            <form action="{{ route('penugasan.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="prodi_id" class="form-label">Pilih Prodi:</label>
                        <select name="prodi_id" id="prodi_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Prodi</option>
                            @foreach($allProdi as $p)
                                <option value="{{ $p->id }}" {{ ($prodiId ?? '') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        {{-- PERBAIKAN: Mengganti dropdown semester --}}
                        <label for="jenis_semester" class="form-label">Pilih Semester:</label>
                        <select name="jenis_semester" id="jenis_semester" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Semester</option>
                            <option value="gasal" {{ ($jenisSemester ?? '') == 'gasal' ? 'selected' : '' }}>Gasal</option>
                            <option value="genap" {{ ($jenisSemester ?? '') == 'genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Form Utama HANYA untuk Simpan Penugasan --}}
    <form action="{{ route('penugasan.store') }}" method="POST">
        @csrf
        {{-- PERBAIKAN: Mengirim kembali parameter filter yang benar --}}
        <input type="hidden" name="jenis_semester" value="{{ $jenisSemester ?? '' }}">
        <input type="hidden" name="prodi_id" value="{{ $prodiId ?? '' }}">

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 25%;">Mata Kuliah (SKS)</th>
                        <th style="width: 15%;">Kelas</th>
                        <th style="width: 45%;">Dosen Pengampu</th>
                        <th style="width: 15%; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mataKuliah as $mk)
                        @php
                            $kelasTerkait = $allKelas->where('semester', $mk->semester)->where('prodi_id', $mk->prodi_id);
                            $dosenTerkait = $allDosen->filter(fn($dosen) => $dosen->prodi->pluck('id')->contains($mk->prodi_id));
                            $penugasanSaatIni = $penugasan->where('mata_kuliah_id', $mk->id);
                            $rowspanCount = $kelasTerkait->count() > 0 ? $kelasTerkait->count() : 1;
                        @endphp

                        @forelse ($kelasTerkait as $kelas)
                            <tr>
                                @if ($loop->first)
                                    <td rowspan="{{ $rowspanCount }}">
                                        <strong>{{ $mk->nama_mk }}</strong> ({{ $mk->sks }} SKS)
                                    </td>
                                @endif
                                
                                <td>{{ $kelas->nama_kelas }}</td>
                                
                                <td>
                                    <div class="input-group">
                                        <input type="text" class="form-control search-dosen-input" placeholder="Cari Nama/NIDN Dosen..." id="search_dosen_{{ $mk->id }}_{{ $kelas->id }}">
                                        <select name="assignments[{{ $mk->id }}_{{ $kelas->id }}][dosen_id]" class="form-select dosen-select" id="dosen_select_{{ $mk->id }}_{{ $kelas->id }}">
                                            <option value="">Pilih Dosen</option>
                                            @foreach($dosenTerkait as $dosen)
                                                @php
                                                    $isSelected = $penugasanSaatIni->where('kelas_id', $kelas->id)->where('dosen_id', $dosen->id)->isNotEmpty();
                                                @endphp
                                                <option value="{{ $dosen->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                    {{ $dosen->nama_dosen }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="hidden" name="assignments[{{ $mk->id }}_{{ $kelas->id }}][mata_kuliah_id]" value="{{ $mk->id }}">
                                    <input type="hidden" name="assignments[{{ $mk->id }}_{{ $kelas->id }}][kelas_id]" value="{{ $kelas->id }}">
                                </td>
                                
                                <td class="text-center">
                                    @php
                                        $penugasanItem = $penugasanSaatIni->where('kelas_id', $kelas->id)->first();
                                    @endphp
                                    @if ($penugasanItem)
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" 
                                                data-url="{{ route('penugasan.destroy', $penugasanItem->id) }}">
                                            Hapus
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td><strong>{{ $mk->nama_mk }}</strong> ({{ $mk->sks }} SKS)</td>
                                <td colspan="3" class="text-center text-muted">
                                    <em>Tidak ada kelas yang ditemukan untuk mata kuliah ini.</em>
                                </td>
                            </tr>
                        @endforelse
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-5">
                                <h5>Data Tidak Ditemukan</h5>
                                <p class="text-muted">Silakan pilih Prodi dan Semester pada filter di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg">Simpan Semua Perubahan</button>
        </div>
    </form>
</div>

{{-- Form tersembunyi KHUSUS untuk HAPUS --}}
<form method="POST" id="delete-form" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Script untuk search dosen ---
        document.querySelectorAll('.search-dosen-input').forEach(input => {
            input.addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase().trim();
                const selectElement = this.nextElementSibling;
                selectElement.querySelectorAll('option').forEach(option => {
                    if (option.value === "") return;
                    option.style.display = option.textContent.toLowerCase().includes(searchValue) ? '' : 'none';
                });
            });
        });

        // --- Script untuk menghandle tombol Hapus ---
        const deleteForm = document.getElementById('delete-form');
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Anda yakin ingin menghapus penugasan ini?')) {
                    const url = this.dataset.url;
                    deleteForm.action = url;
                    deleteForm.submit();
                }
            });
        });
    });
</script>
@endsection
