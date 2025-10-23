<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenugasanController extends Controller
{
    public function index(Request $request)
    {
        $prodiId = $request->input('prodi_id');

        // PERBAIKAN: 'jenis_semester' kini memiliki default 'gasal'
        $jenisSemester = $request->input('jenis_semester', 'gasal');

        $allProdi = Prodi::all();
        $allDosen = Dosen::with('prodi')->get();
        $allKelas = Kelas::all();

        $query = MataKuliah::with(['dosen', 'kelas']);

        if ($prodiId) {
            $query->where('prodi_id', $prodiId);
        }

        // Logika filter untuk semester gasal/genap (sekarang akan selalu aktif)
        if ($jenisSemester === 'gasal') {
            // Gasal adalah semester 1, 3, 5, 7
            $query->whereIn('semester', [1, 3, 5, 7]);
        } elseif ($jenisSemester === 'genap') {
            // Genap adalah semester 2, 4, 6, 8
            $query->whereIn('semester', [2, 4, 6, 8]);
        }

        $mataKuliah = $query->get();
        $penugasan = DB::table('penugasan')->get();

        return view('penugasan', compact('mataKuliah', 'allDosen', 'allKelas', 'penugasan', 'jenisSemester', 'allProdi', 'prodiId'));
    }

    public function store(Request $request)
    {
        $assignments = $request->input('assignments', []);

        $submittedMkIds = array_column($assignments, 'mata_kuliah_id');
        if (!empty($submittedMkIds)) {
            DB::table('penugasan')
                ->whereIn('mata_kuliah_id', $submittedMkIds)
                ->delete();
        }

        foreach ($assignments as $assignment) {
            if (!empty($assignment['dosen_id']) && !empty($assignment['mata_kuliah_id']) && !empty($assignment['kelas_id'])) {
                DB::table('penugasan')->insert([
                    'dosen_id' => $assignment['dosen_id'],
                    'mata_kuliah_id' => $assignment['mata_kuliah_id'],
                    'kelas_id' => $assignment['kelas_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('penugasan.index', [
            'jenis_semester' => $request->jenis_semester,
            'prodi_id' => $request->prodi_id
        ])->with('success', 'Penugasan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('penugasan')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Penugasan berhasil dihapus.');
    }
}
