<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EmployeeImportExportTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_import_users_from_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Department::factory()->create(['code' => 'FTI']);
        Position::factory()->create(['name' => 'Dosen']);
        $csv = implode("\n", [
            'nama_lengkap,gelar_depan,gelar_belakang,jenis_kelamin,tanggal_masuk,tanggal_lahir,nik,npwp,bpjs_kesehatan,bpjs_ketenagakerjaan,nidn,nip,kode_departemen,jabatan,no_hp,alamat_ktp,alamat_rumah,pendidikan_terakhir,perguruan_tinggi,program_studi,email,jatah_cuti,nip_atasan,nama_ibu,gaji_pokok,tunjangan_transport,role,jenis_kepegawaian,status',
            'Ani Wijaya,,M.Kom.,female,2024-01-02,1990-05-10,3273010101900002,,,,1234567890,PEG002,FTI,Dosen,081234567891,Jl. Kampus,Jl. Kampus,S2,Universitas Contoh,Informatika,ani@kampus.ac.id,12,,Siti Aminah,7000000,500000,dosen,permanent,active',
        ]);

        $this->actingAs($admin)->post(route('employees.import'), [
            'file' => UploadedFile::fake()->createWithContent('employees.csv', $csv),
        ])->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', ['nip' => 'PEG002', 'full_name' => 'Ani Wijaya']);
        $this->assertDatabaseHas('users', ['email' => 'ani@kampus.ac.id', 'role' => 'dosen']);
    }

    public function test_export_escapes_spreadsheet_formula_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Employee::factory()->create(['full_name' => '=2+2']);

        $response = $this->actingAs($admin)->get(route('employees.export'));

        $response->assertOk()->assertDownload();
        $this->assertStringContainsString("'=2+2", $response->streamedContent());
    }
}
