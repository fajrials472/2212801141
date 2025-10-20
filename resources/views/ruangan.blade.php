@extends('layouts.app')

@section('title', 'Manajemen Ruangan')

@section('content')
    <h1 class="mb-4">Manajemen Ruangan</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="d-flex justify-content-between mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ruanganModal">
            Tambah Ruangan Baru
        </button>
    </div>

    <form action="{{ route('ruangan.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari ruangan..."
                value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Cari</button>
            <a href="{{ route('ruangan.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div>
        <h2>Daftar Ruangan</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Ruangan</th>
                    <th>Kapasitas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ruangan as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->nama_ruangan }}</td>
                        <td>{{ $item->kapasitas }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm"
                                onclick="editRuangan({{ $item->id }}, '{{ $item->nama_ruangan }}', '{{ $item->kapasitas }}')">Edit</button>
                            <form action="{{ route('ruangan.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $ruangan->links() }}
    </div>

    <div class="modal fade" id="ruanganModal" tabindex="-1" aria-labelledby="ruanganModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ruanganModalLabel">Tambah Ruangan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="ruangan-form" action="{{ route('ruangan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="_method" id="method-input" value="POST">
                        <input type="hidden" name="ruangan_id" id="ruangan-id-input">

                        <div class="mb-3">
                            <label for="nama_ruangan" class="form-label">Nama Ruangan:</label>
                            <input type="text" class="form-control" id="nama_ruangan" name="nama_ruangan" required>
                        </div>
                        <div class="mb-3">
                            <label for="kapasitas" class="form-label">Kapasitas:</label>
                            <input type="number" class="form-control" id="kapasitas" name="kapasitas" min="10"
                                required>
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
        document.addEventListener('DOMContentLoaded', function() {
            const ruanganModal = document.getElementById('ruanganModal');
            ruanganModal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('ruanganModalLabel').innerText = 'Tambah Ruangan Baru';
                document.getElementById('ruangan-form').action = '{{ route('ruangan.store') }}';
                document.getElementById('method-input').value = 'POST';
                document.getElementById('nama_ruangan').value = '';
                document.getElementById('kapasitas').value = '';
                document.getElementById('submit-button').innerText = 'Simpan';
            });
        });

        function editRuangan(id, namaRuangan, kapasitas) {
            document.getElementById('ruanganModalLabel').innerText = 'Edit Ruangan';
            const form = document.getElementById('ruangan-form');

            // Gunakan variabel 'form' yang sudah Anda definisikan
            form.action = `{{ route('ruangan.update', ':id') }}`.replace(':id', id);

            document.getElementById('method-input').value = 'PUT';
            document.getElementById('ruangan-id-input').value = id;
            document.getElementById('nama_ruangan').value = namaRuangan;
            document.getElementById('kapasitas').value = kapasitas;
            document.getElementById('submit-button').innerText = 'Perbarui';

            const modal = new bootstrap.Modal(document.getElementById('ruanganModal'));
            modal.show();
        }
    </script>
@endsection
