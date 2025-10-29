<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Jadwal Kuliah</title>
    <style>
        /* Pengaturan halaman F4 dan margin */
        @page {
            size: 21cm 33cm;
            /* Ukuran kertas F4 */
            margin: 0.2cm 2cm 2cm 2cm;
            /* Atas 0.2cm, lainnya 2cm */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header-table,
        .info-table,
        .schedule-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            padding: 5px;
            vertical-align: middle;
            border: none;
        }

        .logo img {
            width: 80px;
            height: auto;
        }

        .logo {
            width: 80px;
            text-align: center;
        }

        .kop-surat {
            border-bottom: 3px solid black;
            padding-bottom: 10px;
        }

        .kop-surat .text-kop {
            text-align: left;
            padding-left: 20px;
        }

        .kop-surat h1,
        .kop-surat h2 {
            margin: 0;
            font-size: 22px;
            /* Ukuran font besar */
        }

        .kop-surat h2 {
            font-size: 18px;
        }

        .title-bar {
            text-align: center;
            padding: 15px 0 5px 0;
            font-weight: bold;
            font-size: 14px;
            text-decoration: none;
        }

        .info-section {
            padding: 15px 0;
        }

        .info-table td {
            padding: 2px 0;
        }

        .schedule-table {
            margin-bottom: 20px;
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid black;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .schedule-table thead th {
            font-weight: bold;
        }

        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
        }

        .signature-table .right {
            text-align: left;
            padding-left: 100px;
        }

        .signature-space {
            height: 60px;
        }

        /* PERBAIKAN: Definisikan kelas .page-break di sini */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @forelse ($jadwalPerKelas as $namaKelompok => $jadwals)

        {{-- PERBAIKAN: Kelas 'page-break' dihapus dari sini --}}
        <div class="container">
            <div class="kop-surat">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td class="logo">
                            {{-- PERBAIKAN: Path logo disesuaikan --}}
                            <img src="{{ public_path('storage/fakultas.png') }}" alt="Logo">
                        </td>
                        <td class="text-kop">
                            <h1>FAKULTAS TEKNIK</h1>
                            <h2>UNIVERSITAS MUHAMMADIYAH PAREPARE</h2>
                        </td>
                    </tr>
                </table>
            </div>

            <p class="title-bar">JADWAL MATA KULIAH FAKULTAS TEKNIK</p>

            <div class="info-section">
                <table class="info-table">
                    @php
                        // Ambil data Kelas dan Prodi dari item pertama secara aman
                        $firstJadwal = $jadwals->first();
                        $kelas = $firstJadwal?->penugasan?->kelas;
                        $prodiData = $prodi ?? $kelas?->prodi;

                        // Fungsi untuk konversi angka ke Romawi
                        $numberToRoman = function ($number) {
                            $romans = [
                                10 => 'X',
                                9 => 'IX',
                                5 => 'V',
                                4 => 'IV',
                                1 => 'I',
                            ];
                            $result = '';
                            foreach ($romans as $value => $symbol) {
                                while ($number >= $value) {
                                    $result .= $symbol;
                                    $number -= $value;
                                }
                            }
                            return $result;
                        };
                    @endphp

                    {{-- 1. PROGRAM STUDI --}}
                    <tr>
                        <td style="width: 120px;">PROGRAM STUDI</td>
                        <td style="width: 10px;">:</td>
                        <td>{{ strtoupper($prodiData->nama_prodi ?? 'Semua Program Studi') }}
                        </td>
                    </tr>

                    {{-- 2. TAHUN AJARAN --}}
                    <tr>
                        <td style="width: 120px;">TAHUN AJARAN</td>
                        <td style="width: 10px;">:</td>
                        <td>{{ $tahunAjaranAktif ?? '2025/2026' }}</td>
                    </tr>

                    {{-- 3. SEMESTER (Romawi + OPSI Gasal/Genap) --}}
                    <tr>
                        <td style="width: 120px;">SEMESTER</td>
                        <td style="width: 10px;">:</td>
                        <td>
                            @php
                                // Cek apakah filter jenis semester (gasal/genap) tersedia dan ubah huruf pertamanya menjadi kapital
                                $displaySemester =
                                    isset($jenisSemester) && $jenisSemester
                                        ? ucfirst($jenisSemester)
                                        : 'Gasal';
                            @endphp
                            {{ $displaySemester }}
                        </td>
                    </tr>
                </table>
            </div>

            <table class="schedule-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">HARI</th>
                        <th style="width: 10%;">WAKTU</th>
                        <th style="width: 30%;">MATA KULIAH</th>
                        @if (isset($dosen))
                            <th style="width: 15%;">KELAS</th>
                        @endif
                        <th style="width: 25%;">DOSEN</th>
                        <th style="width: 10%;">RUANGAN</th>
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
                        $jadwalGroupedByDay = $jadwals->sortBy(fn($j) => $dayOrder[$j->hari] ?? 99)->groupBy('hari');
                    @endphp

                    @forelse ($jadwalGroupedByDay as $hari => $jadwalsOnThisDay)
                        @foreach ($jadwalsOnThisDay->sortBy('jam-mulai') as $item)
                            <tr>
                                @if ($loop->first)
                                    <td rowspan="{{ count($jadwalsOnThisDay) }}">{{ $hari }}</td>
                                @endif
                                <td>{{ date('H:i', strtotime($item->jam_mulai)) }} -
                                    {{ date('H:i', strtotime($item->jam_selesai)) }}</td>
                                <td style="text-align: left;">{{ $item->penugasan->mataKuliah->nama_mk ?? 'N/A' }}</td>
                                @if (isset($dosen))
                                    <td>{{ $item->penugasan->kelas->nama_kelas ?? 'N/A' }}</td>
                                @endif
                                <td style="text-align: left;">{{ $item->penugasan->dosen->nama_dosen ?? 'N/A' }}</td>
                                <td>{{ $item->ruangan->nama_ruangan ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6">Tidak ada jadwal untuk hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="signature-section">
                <table class="signature-table">
                    <tr>
                        <td></td>
                        <td class="right">Parepare, {{ $tanggalSekarang }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="right">Wakil dekan I</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="signature-space"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="right"><u>Dr. Jasman, ST., MT.</u></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="right">NBM. 933289</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- PERBAIKAN: Pindahkan @if ke sini --}}
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif

    @empty
        <div class="container">
            <p style="text-align: center; padding: 50px;">Tidak ada data jadwal yang bisa dicetak.</p>
        </div>
    @endforelse
</body>

</html>
