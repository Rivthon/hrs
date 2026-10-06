<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerBapController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->role === 'dosen', 403);

        $employee = $request->user()->employee;
        abort_unless($employee, 404, 'Data dosen HRS belum tersedia.');

        $pasLecturer = DB::connection('pas')->table('dosen')
            ->where(function ($query) use ($employee): void {
                $query->when($employee->pas_dosen_id, fn ($query) => $query->where('dosen_id', $employee->pas_dosen_id))
                    ->when($employee->nidn, fn ($query) => $query->orWhere('nidn', $employee->nidn))
                    ->orWhere('email', $employee->email);
            })
            ->first();

        abort_unless($pasLecturer, 404, 'Dosen tidak ditemukan di PAS.');

        if ((int) $employee->pas_dosen_id !== (int) $pasLecturer->dosen_id) {
            $employee->update([
                'pas_dosen_id' => $pasLecturer->dosen_id,
                'pas_kode_dosen' => $pasLecturer->kd_dosen,
            ]);
        }

        $academicYears = DB::connection('pas')->table('tahun_ajaran')
            ->orderByDesc('ta_id')
            ->get(['ta_id', 'nama', 'semester', 'status_ta']);
        $selectedAcademicYearId = $request->integer('ta_id') ?: (int) ($academicYears->firstWhere('status_ta', 1)?->ta_id ?? 0);

        $theory = DB::connection('pas')->table('pertemuan as p')
            ->join('jadwal as j', 'j.id', '=', 'p.jadwal_id')
            ->join('kurikulum as k', 'k.kurikulum_id', '=', 'j.kurikulum_id')
            ->join('matakuliah as m', 'm.matakuliah_id', '=', 'k.matakuliah_id')
            ->where('p.dosen_id', $pasLecturer->dosen_id)
            ->when($selectedAcademicYearId, fn ($query) => $query->where('j.ta_id', $selectedAcademicYearId))
            ->select(['p.pertemuan_id as id', 'p.tanggal_pertemuan', 'p.topik', 'p.sub_topik', 'p.jam_mulai', 'p.jam_selesai', 'p.metode_pbm', 'm.nama as mata_kuliah'])
            ->get()
            ->each(fn ($meeting) => $meeting->jenis = 'Teori');

        $practice = DB::connection('pas')->table('pertemuan_praktik as p')
            ->join('jadwal_praktik as j', 'j.id', '=', 'p.jadwal_praktik_id')
            ->join('kurikulum as k', 'k.kurikulum_id', '=', 'j.kurikulum_id')
            ->join('matakuliah as m', 'm.matakuliah_id', '=', 'k.matakuliah_id')
            ->where('p.dosen_id', $pasLecturer->dosen_id)
            ->when($selectedAcademicYearId, fn ($query) => $query->where('j.ta_id', $selectedAcademicYearId))
            ->select(['p.pertemuan_praktik_id as id', 'p.tanggal_pertemuan', 'p.topik', 'p.sub_topik', 'p.jam_mulai', 'p.jam_selesai', 'p.metode_pbm', 'm.nama as mata_kuliah'])
            ->get()
            ->each(fn ($meeting) => $meeting->jenis = 'Praktik');

        $meetings = $theory->concat($practice)->sortByDesc('tanggal_pertemuan')->values();

        return view('lecturer-bap.index', compact(
            'pasLecturer',
            'academicYears',
            'selectedAcademicYearId',
            'meetings',
        ));
    }
}
