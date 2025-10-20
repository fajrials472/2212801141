<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Jadwal</title>
    {{-- CSS ini diletakkan langsung di sini agar terbaca oleh DOMPDF --}}
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            margin: 0;
        }

        .page {
            padding: 2cm;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: never;
        }

        .kop-surat {
            text-align: center;
            border-bottom: 3px solid black;
            padding-bottom: 10px;
            margin-bottom: 5px;
        }

        .kop-surat h1,
        .kop-surat h2,
        .kop-surat p {
            margin: 0;
        }

        .kop-surat h1 {
            font-size: 18pt;
        }

        .kop-surat h2 {
            font-size: 16pt;
        }

        .judul-utama {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 14pt;
        }

        .info-table {
            margin-bottom: 20px;
            width: 100%;
        }

        .info-table td {
            padding: 2px 0;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        .schedule-table th {
            background-color: #000000;
            color: white;
            font-weight: bold;
        }

        .schedule-table td.text-left {
            text-align: left;
        }

        .ttd {
            margin-top: 50px;
            width: 300px;
            margin-left: auto;
            text-align: left;
        }

        .ttd-space {
            height: 70px;
        }

        .ttd p {
            margin: 0;
        }
    </style>
</head>

<body>
    @foreach ($jadwalDikelompokkan as $namaKelompok => $jadwals)
        <div class="page">
            <div class="kop-surat">
                <h2>FAKULTAS TEKNIK</h2>
                <h1>UNIVERSITAS MUHAMMADIYAH PAREPARE</h1>
            </div>

            <p class="judul-utama">{{ strtoupper($judul) }}</p>

            <table class="info-table">
                <tr>
                    <td style="width: 150px;"><strong>PROGRAM STUDI</strong></td>
                    {{-- Menampilkan nama prodi berdasarkan data yang tersedia --}}
                    <td>:
                        {{ $prodi->nama_prodi ?? ($jadwals->first()->penugasan->kelas->prodi->nama_prodi ?? 'Semua Prodi') }}
                    </td>
                </tr>
                <tr>
                    <td><strong>KELAS</strong></td>
                    <td>: {{ $namaKelompok }}</td>
                </tr>
                {{-- Hanya tampilkan jika sedang mencetak jadwal admin --}}
                @if (isset($jenisSemester))
                    <tr>
                        <td><strong>SEMESTER</strong></td>
                        <td>: {{ ucfirst($jenisSemester) }}</td>
                    </tr>
                @endif
                <tr>
                    <td><strong>KONSENTRASI</strong></td>
                    <td>: .....................................................</td>
                </tr>
            </table>

            <table class="schedule-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">HARI</th>
                        <th style="width: 18%;">WAKTU</th>
                        <th style="width: 30%;">MATA KULIAH</th>
                        <th style="width: 25%;">DOSEN</th>
                        <th style="width: 15%;">RUANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Mengurutkan hari dengan benar
                        $dayOrder = [
                            'Senin' => 1,
                            'Selasa' => 2,
                            'Rabu' => 3,
                            'Kamis' => 4,
                            'Jumat' => 5,
                            'Sabtu' => 6,
                        ];
                        $jadwalGroupedByDay = $jadwals->sortBy(fn($j) => $dayOrder[$j->hari] ?? 99)->groupBy('hari');
                    @endphp
                    @forelse ($jadwalGroupedByDay as $hari => $jadwalsOnThisDay)
                        @foreach ($jadwalsOnThisDay->sortBy('jam_mulai') as $jadwal)
                            <tr>
                                @if ($loop->first)
                                    <td rowspan="{{ count($jadwalsOnThisDay) }}"><strong>{{ $hari }}</strong>
                                    </td>
                                @endif
                                <td>{{ date('H:i', strtotime($jadwal->jam_mulai)) }} -
                                    {{ date('H:i', strtotime($jadwal->jam_selesai)) }}</td>
                                <td class="text-left">{{ $jadwal->penugasan->mataKuliah->nama_mk ?? 'N/A' }}</td>
                                <td class="text-left">{{ $jadwal->penugasan->dosen->nama_dosen ?? 'N/A' }}</td>
                                <td>{{ $jadwal->ruangan->nama_ruangan ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5">Tidak ada jadwal yang tersedia untuk kelas ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="ttd">
                <p>Parepare, {{ now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                <p>Wakil Dekan I,</p>
                <div class="ttd-space"></div>
                <p><strong><u>Dr. Jasman, ST., MT.</u></strong></p>
                <p>NBM. 933289</p>
            </div>
        </div>
    @endforeach
</body>

</html>
