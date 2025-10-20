@extends('layouts.app')

@section('title', 'Manajemen Prodi')

@section('content')
    <h1 class="mb-4">Manajemen Program Studi</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="d-flex justify-content-between mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#prodiModal">
            Tambah Prodi Baru
        </button>
    </div>

    <form action="{{ route('prodi.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama prodi..." value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Cari</button>
            <a href="{{ route('prodi.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div>
        <h2>Daftar Program Studi</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Prodi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prodi as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->nama_prodi }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editProdi({{ $item->id }}, '{{ $item->nama_prodi }}')">Edit</button>
                            <form action="{{ route('prodi.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $prodi->links() }}
    </div>

    <div class="modal fade" id="prodiModal" tabindex="-1" aria-labelledby="prodiModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prodiModalLabel">Tambah Prodi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="prodi-form" action="{{ route('prodi.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="_method" id="method-input" value="POST">
                        <input type="hidden" name="prodi_id" id="prodi-id-input">
                        
                        <div class="mb-3">
                            <label for="nama_prodi" class="form-label">Nama Prodi:</label>
                            <input type="text" class="form-control" id="nama_prodi" name="nama_prodi" required>
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
        document.addEventListener('DOMContentLoaded', function () {
            const prodiModal = document.getElementById('prodiModal');
            prodiModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('prodiModalLabel').innerText = 'Tambah Prodi Baru';
                document.getElementById('prodi-form').action = '{{ route('prodi.store') }}';
                document.getElementById('method-input').value = 'POST';
                document.getElementById('nama_prodi').value = '';
                document.getElementById('submit-button').innerText = 'Simpan';
            });
        });

        function editProdi(id, namaProdi) {
            document.getElementById('prodiModalLabel').innerText = 'Edit Prodi';
            const form = document.getElementById('prodi-form');
            form.action = `{{ route('prodi.update', ':id') }}`.replace(':id', id);
            
            document.getElementById('method-input').value = 'PUT';
            document.getElementById('nama_prodi').value = namaProdi;
            document.getElementById('submit-button').innerText = 'Perbarui';
            
            const modal = new bootstrap.Modal(document.getElementById('prodiModal'));
            modal.show();
        }
    </script>
@endsection