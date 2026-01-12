@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <style>
            .table-matrix {
                table-layout: fixed;
                width: 100%;
            }

            .table-matrix th {
                text-align: center;
                vertical-align: middle;
                background-color: #343a40;
                color: white;
                padding: 10px;
            }

            .col-jam {
                width: 80px;
                background-color: #f8f9fa;
                font-weight: bold;
                text-align: center;
                vertical-align: middle;
                border-right: 2px solid #dee2e6;
            }

            /* Kartu Aman (Kuning) */
            .jadwal-card {
                background-color: #fff3cd;
                border-left: 5px solid #ffc107;
                padding: 5px;
                margin-bottom: 4px;
                border-radius: 4px;
                font-size: 11px;
                text-align: left;
                color: #333;
                box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
            }

            /* Kartu Bentrok (Merah) */
            .jadwal-conflict {
                background-color: #dc3545 !important;
                border-left: 5px solid #8a0e1b !important;
                color: white !important;
            }

            /* Supaya teks di kartu merah tetap putih */
            .jadwal-conflict .mk-name,
            .jadwal-conflict .badge {
                color: white !important;
            }

            .jadwal-conflict .badge-light {
                background-color: rgba(255, 255, 255, 0.2) !important;
                color: white !important;
            }

            .mk-name {
                font-weight: bold;
                display: block;
                font-size: 12px;
                margin-bottom: 2px;
            }
        </style>

        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                <h5 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-calendar-alt"></i> Matrix Jadwal & Uji Bentrok
                </h5>
                <div>
                    <span class="badge badge-warning text-dark p-2 border">
                        <i class="fas fa-check"></i> Jadwal Aman
                    </span>
                    <span class="badge badge-danger p-2 border ml-2">
                        <i class="fas fa-exclamation-triangle"></i> BENTROK
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-matrix mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">JAM</th>
                                @foreach ($hariList as $hari)
                                    <th>{{ strtoupper($hari) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jamList as $jam)
                                <tr>
                                    {{-- KOLOM JAM --}}
                                    <td class="col-jam">{{ $jam }}</td>

                                    {{-- LOOP HARI --}}
                                    @foreach ($hariList as $hari)
                                        @php
                                            $dataCell = $matrix[$hari][$jam];
                                            $jumlahIsi = count($dataCell);

                                            // Array untuk menyimpan ID jadwal yang bermasalah saja
                                            $idYangBentrok = [];

                                            // --- LOGIKA CEK PER ITEM ---
                                            if ($jumlahIsi > 1) {
                                                foreach ($dataCell as $keyA => $itemA) {
                                                    foreach ($dataCell as $keyB => $itemB) {
                                                        if ($itemA->id == $itemB->id) {
                                                            continue;
                                                        } // Jangan cek diri sendiri

                                                        $startA = strtotime($itemA->jam_mulai);
                                                        $endA = strtotime($itemA->jam_selesai);
                                                        $startB = strtotime($itemB->jam_mulai);
                                                        $endB = strtotime($itemB->jam_selesai);

                                                        // 1. Cek Tabrakan Waktu
                                                        if (max($startA, $startB) < min($endA, $endB)) {
                                                            // 2. Cek Resource (Ruangan Sama ATAU Dosen Sama)
                                                            if (
                                                                $itemA->ruangan_id == $itemB->ruangan_id ||
                                                                $itemA->penugasan->dosen_id ==
                                                                    $itemB->penugasan->dosen_id
                                                            ) {
                                                                // CATAT ID KEDUANYA SEBAGAI 'TERSANGKA'
                                                                $idYangBentrok[] = $itemA->id;
                                                                $idYangBentrok[] = $itemB->id;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            // Hapus duplikat ID agar array bersih
                                            $idYangBentrok = array_unique($idYangBentrok);
                                        @endphp

                                        <td class="align-top p-1"
                                            style="height: 60px; background-color: {{ $jumlahIsi > 0 ? '#fafafa' : '#fff' }}">
                                            @if ($jumlahIsi > 0)
                                                @foreach ($dataCell as $item)
                                                    @php
                                                        // Cek apakah jadwal ini ada di daftar 'Tersangka'
                                                        $isConflict = in_array($item->id, $idYangBentrok);
                                                    @endphp

                                                    {{-- KARTU JADWAL --}}
                                                    {{-- Hanya tambahkan class conflict jika ID-nya terdaftar bermasalah --}}
                                                    <div class="jadwal-card {{ $isConflict ? 'jadwal-conflict' : '' }}">

                                                        <span class="mk-name">
                                                            {{ $item->penugasan->mataKuliah->nama_mk }}
                                                        </span>

                                                        <div style="font-size: 10px; opacity: 0.9;">
                                                            <i class="far fa-clock"></i>
                                                            {{ date('H:i', strtotime($item->jam_mulai)) }}-{{ date('H:i', strtotime($item->jam_selesai)) }}
                                                        </div>
                                                        <div class="d-flex justify-content-between mt-1">
                                                            <span
                                                                class="badge {{ $isConflict ? 'badge-light' : 'badge-light text-dark border' }}">
                                                                {{ $item->penugasan->kelas->nama_kelas }}
                                                            </span>
                                                            <span
                                                                class="badge {{ $isConflict ? 'badge-light' : 'badge-dark' }}">
                                                                R. {{ $item->ruangan->nama_ruangan }}
                                                            </span>
                                                        </div>

                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
