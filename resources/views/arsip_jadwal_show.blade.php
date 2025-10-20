@extends('layouts.app')

@section('title', 'Lihat Arsip Jadwal')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Arsip Jadwal: {{ $versi->nama_versi }}</h1>
        <a href="{{ route('jadwal.index') }}" class="btn btn-secondary mb-4">Kembali ke Jadwal Aktif</a>

        @if ($jadwal->isNotEmpty())
            <div class="card w-100" style="min-width: 1200px;">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title mb-0">Detail Arsip Jadwal</h3>
                </div>
                <div class="card-body">
                    @foreach ($jadwal as $namaKelas => $jadwalsByKelas)
                        <div class="mb-5">
                            <h4 class="mb-3">Kelas: <strong>{{ $namaKelas }}</strong></h4>
                            <div class="table-responsive">
                                <table class="table table-bordered text-center align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 10%;">HARI</th>
                                            <th style="width: 15%;">WAKTU</th>
                                            <th style="width: 30%;">MATA KULIAH</th>
                                            <th style="width: 25%;">DOSEN</th>
                                            <th style="width: 20%;">RUANGAN</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $dayOrder = [
                                                'Senin' => 1,
                                                'Selasa' => 2,
                                                'Rabu' => 3,
                                                'Kamis' => 4,
                                                'Jumat' => 5,
                                                'Sabtu' => 6,
                                            ];
                                            $jadwalGroupedByDay = $jadwalsByKelas
                                                ->sortBy(function ($jadwal) use ($dayOrder) {
                                                    return $dayOrder[$jadwal->hari] ?? 99;
                                                })
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
                                                        {{ $item->nama_mk ?? 'N/A' }} <br>
                                                        <small class="text-muted">Semester:
                                                            {{ $item->semester ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="text-start">{{ $item->nama_dosen ?? 'N/A' }}</td>
                                                    <td>{{ $item->nama_ruangan ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="5" class="p-5">Tidak ada jadwal untuk kelas ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                <h4>Arsip Jadwal Kosong.</h4>
            </div>
        @endif
    </div>
@endsection
