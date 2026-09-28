<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeImportTemplateController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, [
                'nama_lengkap', 'gelar_depan', 'gelar_belakang', 'jenis_kelamin', 'tanggal_masuk',
                'tanggal_lahir', 'nik', 'npwp', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan', 'nidn',
                'nip', 'kode_departemen', 'jabatan', 'no_hp', 'alamat_ktp', 'alamat_rumah',
                'pendidikan_terakhir', 'perguruan_tinggi', 'program_studi', 'email', 'jatah_cuti',
                'nip_atasan', 'nama_ibu', 'gaji_pokok', 'tunjangan_transport', 'role',
                'jenis_kepegawaian', 'status',
            ]);
            fputcsv($stream, [
                'Budi Santoso', '', 'S.Kom.', 'male', '2024-01-02', '1990-05-10',
                '3273010101900001', '', '', '', '', 'PEG001', 'SDM', 'Staf Administrasi',
                '081234567890', 'Jl. Kampus No. 1', 'Jl. Kampus No. 1', 'S1', 'Universitas Contoh',
                'Sistem Informasi', 'budi@kampus.ac.id', '12', '', 'Siti Aminah', '5000000',
                '500000', 'staff', 'permanent', 'active',
            ]);
            fclose($stream);
        }, 'template-import-pegawai.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
