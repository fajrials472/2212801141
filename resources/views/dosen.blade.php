@extends('layouts.app')

@section('title', 'Manajemen Dosen')

@section('content')
    <h1 class="mb-4">Manajemen Dosen</h1>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
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
            <h5 class="card-title mb-0">Daftar Dosen</h5>
            <div class="d-flex">
                <form action="{{ route('dosen.index') }}" method="GET" class="me-2">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari dosen..."
                            value="{{ request('search') }}">
                        <button class="btn btn-secondary" type="submit">Cari</button>
                    </div>
                </form>
                {{-- PERBAIKAN 1: Mengganti nama fungsi onclick --}}
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dosenModal"
                    onclick="setupTambahModal()">
                    Tambah Dosen
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter Prodi -->
            <form action="{{ route('dosen.index') }}" method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label for="prodi_id" class="form-label">Filter Prodi</label>
                        <select name="prodi_id" id="prodi_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Semua Prodi</option>
                            @foreach ($prodi as $prodi_item)
                                <option value="{{ $prodi_item->id }}"
                                    {{ ($prodiId ?? '') == $prodi_item->id ? 'selected' : '' }}>
                                    {{ $prodi_item->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 d-flex align-items-end">
                        <a href="{{ route('dosen.index') }}" class="btn btn-secondary">Reset Filter</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nama Dosen</th>
                            <th>Alamat</th>
                            <th>NBM</th>
                            <th>NIDN</th>
                            <th>Email</th>
                            <th>Prodi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosen as $item)
                            <tr>
                                <td>{{ ($dosen->currentPage() - 1) * $dosen->perPage() + $loop->iteration }}</td>
                                <td>{{ $item->nama_dosen }}</td>
                                <td>{{ $item->alamat ?? '-' }}</td>
                                <td>{{ $item->nbm ?? '-' }}</td>
                                <td>{{ $item->nidn ?? '-' }}</td>
                                <td>{{ $item->email ?? '-' }}</td>
                                <td>
                                    {{ $item->prodi->map(fn($p) => $p->nama_prodi)->implode(', ') }}
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-sm"
                                        onclick="editDosen({{ $item->id }}, '{{ addslashes($item->nama_dosen) }}', '{{ addslashes($item->alamat ?? '') }}', '{{ $item->nbm ?? '' }}', '{{ $item->nidn ?? '' }}', '{{ $item->email ?? '' }}', {{ json_encode($item->prodi->pluck('id')) }})">
                                        Edit
                                    </button>
                                    <form action="{{ route('dosen.destroy', $item->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus dosen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data dosen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $dosen->links() }}
            </div>
        </div>
    </div>


    <div class="modal fade" id="dosenModal" tabindex="-1" aria-labelledby="dosenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dosenModalLabel">Tambah Dosen Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="dosen-form" action="{{ route('dosen.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="_method" id="method-input" value="POST">

                        <div class="mb-3">
                            <label for="nama_dosen" class="form-label">Nama Dosen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_dosen" name="nama_dosen" required>
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="alamat" name="alamat" required>
                        </div>

                        <div class="mb-3">
                            <label for="nbm" class="form-label">NBM</label>
                            <input type="text" class="form-control" id="nbm" name="nbm">
                        </div>
                        <div class="mb-3">
                            <label for="nidn" class="form-label">NIDN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nidn" name="nidn" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="prodi" class="form-label">Prodi <span class="text-danger">*</span></label>
                            <div id="prodi-checkbox-list"
                                style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px; border-radius: .25rem;">
                                @foreach ($prodi as $prodi_item)
                                    <div class="form-check">
                                        <input class="form-check-input prodi-checkbox" type="checkbox" name="prodi[]"
                                            value="{{ $prodi_item->id }}" id="prodi_{{ $prodi_item->id }}">
                                        <label class="form-check-label" for="prodi_{{ $prodi_item->id }}">
                                            {{ $prodi_item->nama_prodi }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
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
        function setupTambahModal() {
            const form = document.getElementById('dosen-form');
            form.reset();
            form.action = '{{ route('dosen.store') }}';

            document.getElementById('method-input').value = 'POST';
            document.getElementById('dosenModalLabel').innerText = 'Tambah Dosen Baru';
            document.getElementById('submit-button').innerText = 'Simpan';

            document.querySelectorAll('.prodi-checkbox').forEach(cb => cb.checked = false);
        }

        function editDosen(id, namaDosen, alamat, nbm, nidn, email, prodiIds) {
            try {
                const form = document.getElementById('dosen-form');
                form.reset();

                document.getElementById('dosenModalLabel').innerText = 'Edit Dosen';

                const updateUrlTmpl = @json(route('dosen.update', ['dosen' => '__ID__']));
                form.action = updateUrlTmpl.replace('__ID__', id);

                document.getElementById('method-input').value = 'PUT';
                document.getElementById('nama_dosen').value = namaDosen;
                document.getElementById('alamat').value = alamat;
                document.getElementById('nbm').value = nbm || '';
                document.getElementById('nidn').value = nidn;
                document.getElementById('email').value = email;
                document.getElementById('submit-button').innerText = 'Perbarui';

                document.querySelectorAll('.prodi-checkbox').forEach(cb => {
                    cb.checked = prodiIds.includes(parseInt(cb.value));
                });

                const modalElement = document.getElementById('dosenModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            } catch (error) {
                console.error("Terjadi error di dalam fungsi editDosen:", error);
            }
        }
    </script>
@endsection
