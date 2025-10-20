@extends('layouts.app')

@section('title', 'Manajemen Mata Kuliah')

@section('content')
    <h1 class="mb-4">Manajemen Mata Kuliah</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="d-flex justify-content-between mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mataKuliahModal">
            Tambah Mata Kuliah Baru
        </button>
    </div>

    <!-- Formulir Filter dan Pencarian -->
    <form action="{{ route('mata-kuliah.index') }}" method="GET" class="mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="prodi_id" class="form-label">Filter Prodi</label>
                <select name="prodi_id" id="prodi_id" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Prodi</option>
                    @foreach ($prodi as $prodi_item)
                        <option value="{{ $prodi_item->id }}" {{ ($prodiId ?? '') == $prodi_item->id ? 'selected' : '' }}>
                            {{ $prodi_item->nama_prodi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="jenis_semester" class="form-label">Filter Semester</label>
                <select name="jenis_semester" id="jenis_semester" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Semester</option>
                    <option value="gasal" {{ ($jenisSemester ?? '') == 'gasal' ? 'selected' : '' }}>Gasal</option>
                    <option value="genap" {{ ($jenisSemester ?? '') == 'genap' ? 'selected' : '' }}>Genap</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Cari Nama/Kode</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Cari mata kuliah..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-secondary" type="submit">Cari</button>
                <a href="{{ route('mata-kuliah.index') }}" class="btn btn-outline-secondary ms-2">Reset</a>
            </div>
        </div>
    </form>

    <div>
        <h2>Daftar Mata Kuliah</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode MK</th>
                    <th>Nama MK</th>
                    <th>SKS</th>
                    <th>Prodi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mataKuliah as $item)
                    <tr>
                        <td>{{ ($mataKuliah->currentPage() - 1) * $mataKuliah->perPage() + $loop->iteration }}</td>
                        <td>{{ $item->kode_mk }}</td>
                        <td>{{ $item->nama_mk }}</td>
                        <td>{{ $item->sks }}</td>
                        <td>{{ $item->prodi->nama_prodi }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm"
                                onclick="editMataKuliah({{ $item->id }}, '{{ addslashes($item->nama_mk) }}', '{{ $item->kode_mk }}', '{{ $item->sks }}', '{{ $item->semester }}', '{{ $item->prodi_id }}')">Edit</button>
                            <form action="{{ route('mata-kuliah.destroy', $item->id) }}" method="POST" class="d-inline">
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
        {{ $mataKuliah->links() }}
    </div>

    <!-- Modal Form Tambah/Edit Mata Kuliah -->
    <div class="modal fade" id="mataKuliahModal" tabindex="-1" aria-labelledby="mataKuliahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mataKuliahModalLabel">Tambah Mata Kuliah Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Form untuk Tambah Massal -->
                <form id="mataKuliah-form-add" action="{{ route('mata-kuliah.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="prodi_id_add" class="form-label">Prodi:</label>
                                <select class="form-control" id="prodi_id_add" name="prodi_id" required>
                                    @foreach ($prodi as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="semester_add" class="form-label">Semester:</label>
                                <input type="number" class="form-control" id="semester_add" name="semester" min="1"
                                    max="14" required>
                            </div>
                        </div>
                        <hr>
                        <div id="mata-kuliah-fields">
                            <!-- Field Mata Kuliah Pertama -->
                            <div class="row mb-3 mata-kuliah-item">
                                <div class="col-md-4">
                                    <label for="nama_mk_0" class="form-label">Nama Mata Kuliah:</label>
                                    <input type="text" class="form-control" id="nama_mk_0"
                                        name="mata_kuliahs[0][nama_mk]" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="kode_mk_0" class="form-label">Kode MK:</label>
                                    <input type="text" class="form-control" id="kode_mk_0"
                                        name="mata_kuliahs[0][kode_mk]" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="sks_0" class="form-label">SKS:</label>
                                    <input type="number" class="form-control" id="sks_0"
                                        name="mata_kuliahs[0][sks]" min="1" max="4" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger remove-mata-kuliah-btn"
                                        style="display:none;">Hapus</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" id="add-mata-kuliah-btn">Tambah Mata
                            Kuliah</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="submit-button-add">Simpan Semua</button>
                    </div>
                </form>
                <!-- Form untuk Edit Tunggal -->
                <form id="mataKuliah-form-edit" action="" method="POST" style="display:none;">
                    @csrf
                    <input type="hidden" name="_method" id="method-input-edit" value="PUT">
                    <div class="modal-body">
                        <input type="hidden" name="mata_kuliah_id" id="mata-kuliah-id-input-edit">

                        <div class="mb-3">
                            <label for="nama_mk_edit" class="form-label">Nama Mata Kuliah:</label>
                            <input type="text" class="form-control" id="nama_mk_edit" name="nama_mk" required>
                        </div>
                        <div class="mb-3">
                            <label for="kode_mk_edit" class="form-label">Kode Mata Kuliah:</label>
                            <input type="text" class="form-control" id="kode_mk_edit" name="kode_mk" required>
                        </div>
                        <div class="mb-3">
                            <label for="sks_edit" class="form-label">SKS:</label>
                            <input type="number" class="form-control" id="sks_edit" name="sks" min="1"
                                max="4" required>
                        </div>
                        <div class="mb-3">
                            <label for="semester_edit" class="form-label">Semester:</label>
                            <input type="number" class="form-control" id="semester_edit" name="semester"
                                min="1" max="14" required>
                        </div>
                        <div class="mb-3">
                            <label for="prodi_id_edit" class="form-label">Prodi:</label>
                            <select class="form-control" id="prodi_id_edit" name="prodi_id" required>
                                @foreach ($prodi as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="submit-button-edit">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let mkCount = 1;
        document.getElementById('add-mata-kuliah-btn').addEventListener('click', function() {
            const container = document.getElementById('mata-kuliah-fields');
            const newField = document.createElement('div');
            newField.className = 'row mb-3 mata-kuliah-item';
            newField.innerHTML = `
                <div class="col-md-4">
                    <label for="nama_mk_${mkCount}" class="form-label">Nama Mata Kuliah:</label>
                    <input type="text" class="form-control" id="nama_mk_${mkCount}" name="mata_kuliahs[${mkCount}][nama_mk]" required>
                </div>
                <div class="col-md-3">
                    <label for="kode_mk_${mkCount}" class="form-label">Kode MK:</label>
                    <input type="text" class="form-control" id="kode_mk_${mkCount}" name="mata_kuliahs[${mkCount}][kode_mk]" required>
                </div>
                <div class="col-md-2">
                    <label for="sks_${mkCount}" class="form-label">SKS:</label>
                    <input type="number" class="form-control" id="sks_${mkCount}" name="mata_kuliahs[${mkCount}][sks]" min="1" max="4" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-mata-kuliah-btn">Hapus</button>
                </div>
            `;
            container.appendChild(newField);

            newField.querySelector('.remove-mata-kuliah-btn').addEventListener('click', function() {
                newField.remove();
            });

            mkCount++;
        });

        document.querySelectorAll('.remove-mata-kuliah-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                btn.closest('.mata-kuliah-item').remove();
            });
        });

        function editMataKuliah(id, namaMk, kodeMk, sks, semester, prodiId) {
            document.getElementById('mataKuliahModalLabel').innerText = 'Edit Mata Kuliah';
            document.getElementById('mataKuliah-form-add').style.display = 'none';
            const formEdit = document.getElementById('mataKuliah-form-edit');
            formEdit.style.display = 'block';
            formEdit.action = `{{ route('mata-kuliah.update', ':id') }}`.replace(':id', id);

            document.getElementById('method-input-edit').value = 'PUT';
            document.getElementById('mata-kuliah-id-input-edit').value = id;
            document.getElementById('nama_mk_edit').value = namaMk;
            document.getElementById('kode_mk_edit').value = kodeMk;
            document.getElementById('sks_edit').value = sks;
            document.getElementById('semester_edit').value = semester;
            document.getElementById('prodi_id_edit').value = prodiId;

            const modal = new bootstrap.Modal(document.getElementById('mataKuliahModal'));
            modal.show();
        }

        document.getElementById('mataKuliahModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('mataKuliahModalLabel').innerText = 'Tambah Mata Kuliah Baru';
            document.getElementById('mataKuliah-form-add').style.display = 'block';
            document.getElementById('mataKuliah-form-edit').style.display = 'none';
        });
    </script>
@endsection
