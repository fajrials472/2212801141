@extends('layouts.app')

@section('title', 'Manajemen Mahasiswa')

@section('content')
    <h1 class="mb-4">Manajemen Mahasiswa</h1>

    {{-- Notifikasi Sukses --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Notifikasi Error --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi Kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Mahasiswa</h5>
            <div class="d-flex">
                <form action="{{ route('mahasiswa.index') }}" method="GET" class="me-2">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari mahasiswa..."
                            value="{{ request('search') }}">
                        <button class="btn btn-secondary" type="submit">Cari</button>
                    </div>
                </form>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mahasiswaModal"
                    onclick="tambahMahasiswa()">
                    Tambah Mahasiswa
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mahasiswa as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->nim }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>
                                    @forelse($item->kelas as $kelas)
                                        <span class="badge bg-success">{{ $kelas->nama_kelas }}</span>
                                    @empty
                                        N/A
                                    @endforelse
                                </td>
                                <td>{{ $item->alamat ?? '-' }}</td>

                                <td>
                                    <button class="btn btn-warning btn-sm"
                                        onclick="editMahasiswa({{ $item->id }}, '{{ addslashes($item->nama) }}', '{{ $item->nim }}', '{{ addslashes($item->alamat ?? '') }}')">
                                        Edit
                                    </button>

                                    {{-- PERBAIKAN: Tombol untuk Buat User --}}
                                    @if (is_null($item->user_id))
                                        <form action="{{ route('mahasiswa.createUser', $item->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Anda yakin ingin membuat akun user untuk {{ addslashes($item->nama) }}? Email akan dibuat dari NIM dan password default adalah \'password123\'.')">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">Buat User</button>
                                        </form>
                                    @endif

                                    <form action="{{ route('mahasiswa.destroy', $item->id) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- Pastikan colspan adalah 6 --}}
                                <td colspan="6" class="text-center">Tidak ada data mahasiswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $mahasiswa->links() }}
            </div>
        </div>
    </div>


    <div class="modal fade" id="mahasiswaModal" tabindex="-1" aria-labelledby="mahasiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mahasiswaModalLabel">Tambah Mahasiswa Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="mahasiswa-form" action="{{ route('mahasiswa.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="_method" id="method-input" value="POST">
                        <input type="hidden" name="mahasiswa_id" id="mahasiswa-id-input">

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nim" name="nim" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <input type="text" class="form-control" id="alamat" name="alamat">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="submit-button">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function tambahMahasiswa() {
            const form = document.getElementById('mahasiswa-form');
            form.reset();
            form.action = '{{ route('mahasiswa.store') }}';

            document.getElementById('method-input').value = 'POST';
            document.getElementById('mahasiswaModalLabel').innerText = 'Tambah Mahasiswa Baru';
            document.getElementById('submit-button').innerText = 'Simpan';
        }

        function editMahasiswa(id, nama, nim, alamat) {
            try {
                const form = document.getElementById('mahasiswa-form');
                form.reset();

                document.getElementById('mahasiswaModalLabel').innerText = 'Edit Mahasiswa';
                form.action = `{{ route('mahasiswa.update', ':id') }}`.replace(':id', id);

                document.getElementById('method-input').value = 'PUT';
                document.getElementById('mahasiswa-id-input').value = id;
                document.getElementById('nama').value = nama;
                document.getElementById('nim').value = nim;
                document.getElementById('alamat').value = alamat;
                document.getElementById('submit-button').innerText = 'Perbarui';

                const modalElement = document.getElementById('mahasiswaModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            } catch (error) {
                console.error("Terjadi error di dalam fungsi editMahasiswa:", error);
            }
        }
    </script>
@endsection
