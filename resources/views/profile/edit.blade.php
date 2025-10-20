@extends('layouts.app')

@section('title', 'Edit Profil Saya')

@section('content')
    <h1 class="mb-4">Profil Saya</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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

    <div class="card">
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        @php
                            // Ambil profil yang sesuai (dosen atau mahasiswa)
                            $profile = $user->role === 'dosen' ? $user->dosen : $user->mahasiswa;
                            $fotoUrl = $profile->foto ? asset('storage/' . $profile->foto) : 'https://via.placeholder.com/150';
                        @endphp
                        <img src="{{ $fotoUrl }}" alt="Foto Profil" class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <h5 class="mt-3">{{ $user->name }}</h5>
                        <p class="text-muted">
                            @if($user->role === 'dosen')
                                NIDN: {{ $user->nidn }}
                            @else
                                NIM: {{ $user->nim }}
                            @endif
                        </p>
                    </div>

                    <div class="col-md-8">
                        <h4>Data Diri</h4>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3">{{ old('alamat', $profile->alamat) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $profile->tempat_lahir) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $profile->tanggal_lahir) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="no_hp" class="form-label">No. HP</label>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" value="{{ old('no_hp', $profile->no_hp) }}">
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Ubah Foto Profil</label>
                            <input class="form-control" type="file" id="foto" name="foto">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah foto. Maksimal 2MB.</small>
                        </div>
                        
                        <hr class="my-4">

                        <h4>Ubah Password</h4>
                         <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                            </div>
                             <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

