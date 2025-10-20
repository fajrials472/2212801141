@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('content')
    <h1 class="mb-4">Manajemen Kelas</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi Kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kelasModal" onclick="tambahKelas()">
            Tambah Kelas Baru
        </button>
    </div>

    <form action="{{ route('kelas.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari kelas..." value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Cari</button>
            <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div>
        <h2>Daftar Kelas</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Angkatan</th>
                    <th>Nama Kelas</th>
                    <th>Jumlah Mahasiswa</th>
                    <th>Prodi</th>
                    <th>Semester</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kelas as $item)
                    <tr>
                        <td>{{ ($kelas->currentPage() - 1) * $kelas->perPage() + $loop->iteration }}</td>
                        <td>{{ $item->angkatan }}</td>
                        <td>{{ $item->nama_kelas }}</td>
                        <td>{{ $item->jumlah_mahasiswa }}</td>
                        <td>{{ $item->prodi->nama_prodi }}</td>
                        <td>{{ $item->semester }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm"
                                onclick="editKelas({{ $item->id }}, '{{ addslashes($item->nama_kelas) }}', '{{ addslashes($item->angkatan) }}', '{{ $item->jumlah_mahasiswa }}', '{{ $item->prodi_id }}', '{{ $item->semester }}', {{ json_encode($item->mahasiswa->pluck('id')) }})">
                                Edit
                            </button>
                            <form action="{{ route('kelas.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $kelas->links() }}
    </div>

    <div class="modal fade" id="kelasModal" tabindex="-1" aria-labelledby="kelasModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kelasModalLabel">Tambah Kelas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="kelas-form" action="{{ route('kelas.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="_method" id="method-input" value="POST">
                        <input type="hidden" name="kelas_id" id="kelas-id-input">

                        <div class="mb-3">
                            <label for="angkatan" class="form-label">Angkatan:</label>
                            <input type="text" class="form-control" id="angkatan" name="angkatan" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_kelas" class="form-label">Nama Kelas:</label>
                            <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prodi_id" class="form-label">Prodi:</label>
                            <select class="form-control" id="prodi_id" name="prodi_id" required>
                                @foreach($prodi as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester:</label>
                            <input type="number" class="form-control" id="semester" name="semester" min="1" max="14" required>
                        </div>
                        <div class="mb-3">
                            <label for="search_mahasiswa" class="form-label">Cari Mahasiswa:</label>
                            <input type="text" class="form-control" id="search_mahasiswa" placeholder="Cari nama atau NIM...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mahasiswa <span class="text-danger">*</span></label>
                            <div id="mahasiswa-checkbox-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px; border-radius: .25rem;">
                                <!-- Checkbox mahasiswa akan dimuat di sini oleh JavaScript -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah_mahasiswa" class="form-label">Jumlah Mahasiswa</label>
                            <input type="number" class="form-control" id="jumlah_mahasiswa" name="jumlah_mahasiswa" readonly>
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
        let allMahasiswa = @json($mahasiswa);
        let mahasiswaCheckboxList = document.getElementById('mahasiswa-checkbox-list');
        let jumlahMahasiswaInput = document.getElementById('jumlah_mahasiswa');

        // Mengurutkan mahasiswa berdasarkan NIM saat halaman dimuat
        allMahasiswa.sort((a, b) => {
            return parseInt(a.nim) - parseInt(b.nim);
        });

        function renderMahasiswaCheckboxes(filteredMahasiswa = allMahasiswa, checkedIds = []) {
            mahasiswaCheckboxList.innerHTML = '';
            filteredMahasiswa.forEach(m => {
                const isChecked = checkedIds.includes(m.id);
                const isAssigned = m.kelas && m.kelas.length > 0;
                
                const div = document.createElement('div');
                div.className = 'form-check';
                
                const checkbox = document.createElement('input');
                checkbox.className = 'form-check-input mahasiswa-checkbox';
                checkbox.type = 'checkbox';
                checkbox.name = 'mahasiswa[]';
                checkbox.value = m.id;
                checkbox.id = 'mahasiswa_' + m.id;
                
                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.htmlFor = 'mahasiswa_' + m.id;
                label.innerText = `${m.nama} (${m.nim})`;
                
                if (isChecked) {
                    checkbox.checked = true;
                }
                
                // Menonaktifkan checkbox jika mahasiswa sudah terdaftar di kelas lain
                if (isAssigned && !isChecked) {
                    checkbox.disabled = true;
                    label.title = `Mahasiswa ini sudah terdaftar di kelas lain: ${m.kelas[0].nama_kelas}`;
                    label.style.color = '#6c757d';
                }

                div.appendChild(checkbox);
                div.appendChild(label);
                mahasiswaCheckboxList.appendChild(div);
            });
            updateJumlahMahasiswa();
        }

        function updateJumlahMahasiswa() {
            const checkedBoxes = document.querySelectorAll('.mahasiswa-checkbox:checked');
            jumlahMahasiswaInput.value = checkedBoxes.length;
        }

        document.getElementById('search_mahasiswa').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const checkedIds = Array.from(document.querySelectorAll('.mahasiswa-checkbox:checked')).map(cb => parseInt(cb.value));

            const filteredMahasiswa = allMahasiswa.filter(m => 
                m.nama.toLowerCase().includes(searchValue) || 
                m.nim.toLowerCase().includes(searchValue) ||
                checkedIds.includes(m.id)
            );
            
            renderMahasiswaCheckboxes(filteredMahasiswa, checkedIds);
        });

        document.addEventListener('DOMContentLoaded', function() {
            renderMahasiswaCheckboxes();

            const kelasModal = document.getElementById('kelasModal');
            kelasModal.addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('kelas-form');
                form.reset();
                form.action = '{{ route('kelas.store') }}';
                document.getElementById('method-input').value = 'POST';
                document.getElementById('kelasModalLabel').innerText = 'Tambah Kelas Baru';
                document.getElementById('submit-button').innerText = 'Simpan';
                
                renderMahasiswaCheckboxes();
                document.getElementById('search_mahasiswa').value = '';
            });

            mahasiswaCheckboxList.addEventListener('change', updateJumlahMahasiswa);
        });

        function editKelas(id, namaKelas, angkatan, jumlahMahasiswa, prodiId, semester, mahasiswaIds) {
            const form = document.getElementById('kelas-form');
            form.action = `{{ route('kelas.update', ':id') }}`.replace(':id', id);
            document.getElementById('method-input').value = 'PUT';
            document.getElementById('kelasModalLabel').innerText = 'Edit Kelas';
            document.getElementById('nama_kelas').value = namaKelas;
            document.getElementById('angkatan').value = angkatan;
            document.getElementById('prodi_id').value = prodiId;
            document.getElementById('semester').value = semester;
            document.getElementById('jumlah_mahasiswa').value = jumlahMahasiswa;
            
            renderMahasiswaCheckboxes(allMahasiswa, mahasiswaIds);

            const modalElement = document.getElementById('kelasModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        }
    </script>
@endsection
