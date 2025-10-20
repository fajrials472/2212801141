<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Data - {{ $judul }}</title>
    <style>
        /* PERBAIKAN: Menyesuaikan margin halaman */
        @page {
            size: 21cm 33cm;
            /* Ukuran kertas F4 */
            margin: 0.3cm 2cm 2cm 2cm;
            /* Atas 0.3cm, lainnya 2cm */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            /* Ukuran font dasar diperbesar */
            color: #000;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .kop-surat {
            text-align: center;
            border-bottom: 3px solid black;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .kop-surat h1,
        .kop-surat h2 {
            margin: 0;
        }

        /* PERBAIKAN: Ukuran font kop surat diperbesar */
        .kop-surat h1 {
            font-size: 22px;
        }

        .kop-surat h2 {
            font-size: 18px;
        }

        .title {
            text-align: center;
            padding: 10px 0;
            font-weight: bold;
            font-size: 14px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid black;
            padding: 6px;
            text-align: left;
        }

        .data-table th {
            font-weight: bold;
            text-align: center;
        }

        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
            /* Mencegah TTD terpisah halaman */
        }

        .signature-table {
            width: 100%;
        }

        .signature-table td {
            width: 50%;
        }

        .signature-table .right {
            text-align: left;
            padding-left: 150px;
        }

        .signature-space {
            height: 60px;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- Kop Surat --}}
        <div class="kop-surat">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 90px; text-align: center;">
                        <img src="{{ public_path('storage/images/logo.png') }}" alt="Logo" style="width: 80px;">
                    </td>
                    <td style="text-align: center;">
                        <h1>FAKULTAS TEKNIK</h1>
                        <h2>UNIVERSITAS MUHAMMADIYAH PAREPARE</h2>
                    </td>
                    <td style="width: 90px;"></td>
                </tr>
            </table>
        </div>

        <p class="title">{{ strtoupper($judul) }}</p>

        @if (!empty($subJudul))
            <p style="text-align: center; margin-top: -10px; margin-bottom: 20px;">{{ implode(' - ', $subJudul) }}</p>
        @endif


        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    @foreach ($kolom as $namaKolom => $field)
                        <th>{{ $namaKolom }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        @foreach ($kolom as $namaKolom => $field)
                            <td>
                                @if ($field === 'user.kelas.nama_kelas')
                                    {{ data_get($item, 'user.kelas.nama_kelas', 'N/A') }}
                                @elseif($field === 'prodi_list')
                                    {{ $item->prodi->map(fn($p) => $p->nama_prodi)->implode(', ') }}
                                @else
                                    {{ data_get($item, $field, 'N/A') }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($kolom) + 1 }}" style="text-align: center; padding: 20px;">
                            Tidak ada data yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Tanda Tangan --}}
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td></td>
                    <td class="right">Parepare, {{ $tanggalSekarang }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="right">Wakil Dekan I,</td>
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
</body>

</html>
