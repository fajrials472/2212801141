@php
    $timeSlots = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00'];
    $hariKuliah = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
@endphp

@if(empty($jadwalGrid))
    <div class="alert alert-info mt-4">Tidak ada jadwal yang tersedia.</div>
@else
    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th scope="col">Jam</th>
                <th scope="col">Senin</th>
                <th scope="col">Selasa</th>
                <th scope="col">Rabu</th>
                <th scope="col">Kamis</th>
                <th scope="col">Jumat</th>
                <th scope="col">Sabtu</th>
                <th scope="col">Minggu</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($timeSlots as $time)
                <tr>
                    <th scope="row">{{ $time }}</th>
                    @foreach ($hariKuliah as $hari)
                        <td class="align-middle">
                            @if (isset($jadwalGrid[$hari][$time]))
                                @php
                                    $jadwal = $jadwalGrid[$hari][$time];
                                @endphp
                                <div class="bg-light p-2 rounded">
                                    <strong>{{ $jadwal->mataKuliah->nama_mk }}</strong>
                                    <br>
                                    <small>{{ $jadwal->dosen->nama_dosen }}</small>
                                    <br>
                                    <small>{{ $jadwal->kelas->nama_kelas }} - {{ $jadwal->ruangan->nama_ruangan }}</small>
                                    <br>
                                    <small>{{ date('H:i', strtotime($jadwal->jam_mulai)) }} - {{ date('H:i', strtotime($jadwal->jam_selesai)) }}</small>
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
