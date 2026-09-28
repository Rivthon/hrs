<?php

namespace App\Http\Controllers;

use App\Actions\SaveEmployeeAction;
use App\Http\Requests\ImportEmployeesRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

class EmployeeImportController extends Controller
{
    public function store(ImportEmployeesRequest $request, SaveEmployeeAction $saveEmployee): RedirectResponse
    {
        $file = $request->file('file');
        $stream = fopen($file->getRealPath(), 'r');

        if ($stream === false) {
            return back()->withErrors(['file' => 'File tidak dapat dibaca.']);
        }

        $firstLine = fgets($stream);

        if ($firstLine === false) {
            fclose($stream);

            return back()->withErrors(['file' => 'File CSV tidak memiliki header.']);
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $header = str_getcsv($firstLine, $delimiter);

        $header = array_map(fn (string $column): string => Str::slug(ltrim($column, "\xEF\xBB\xBF"), '_'), $header);
        $requiredHeaders = ['nama_lengkap', 'jenis_kelamin', 'tanggal_masuk', 'tanggal_lahir', 'nik', 'nip', 'kode_departemen', 'jabatan', 'no_hp', 'alamat_ktp', 'pendidikan_terakhir', 'perguruan_tinggi', 'program_studi', 'email', 'nama_ibu', 'role'];

        if (array_diff($requiredHeaders, $header) !== []) {
            fclose($stream);

            return back()->withErrors(['file' => 'Format kolom CSV tidak sesuai template manajemen pengguna.']);
        }

        $rows = [];
        while (($values = fgetcsv($stream, null, $delimiter)) !== false) {
            if (count($values) !== count($header) || collect($values)->filter(fn ($value): bool => trim((string) $value) !== '')->isEmpty()) {
                continue;
            }

            $rows[] = array_combine($header, $values);
        }
        fclose($stream);

        try {
            DB::transaction(function () use ($rows, $saveEmployee): void {
                foreach ($rows as $index => $row) {
                    $this->importRow($row, $index + 2, $saveEmployee);
                }
            });
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        return redirect()->route('employees.index')->with('success', count($rows).' data pengguna berhasil diimpor.');
    }

    /**
     * @param  array<string, string>  $row
     */
    private function importRow(array $row, int $line, SaveEmployeeAction $saveEmployee): void
    {
        $department = Department::where('code', $row['kode_departemen'])->where('is_active', true)->first();
        $position = Position::where('name', $row['jabatan'])->where('is_active', true)->first();
        $supervisorNip = $row['nip_atasan'] ?? null;
        $supervisor = empty($supervisorNip) ? null : Employee::where('nip', $supervisorNip)->first();

        if ($department === null || $position === null) {
            throw new RuntimeException("Baris {$line}: departemen atau jabatan tidak ditemukan.");
        }

        $data = [
            'full_name' => $row['nama_lengkap'],
            'title_prefix' => $row['gelar_depan'] ?? null,
            'title_suffix' => $row['gelar_belakang'] ?? null,
            'gender' => $row['jenis_kelamin'],
            'joined_on' => $row['tanggal_masuk'],
            'date_of_birth' => $row['tanggal_lahir'],
            'nik' => $row['nik'],
            'npwp' => $row['npwp'] ?? null,
            'bpjs_health_number' => $row['bpjs_kesehatan'] ?? null,
            'bpjs_employment_number' => $row['bpjs_ketenagakerjaan'] ?? null,
            'nidn' => $row['nidn'] ?? null,
            'nip' => $row['nip'],
            'department_id' => $department->id,
            'position_id' => $position->id,
            'phone' => $row['no_hp'],
            'identity_address' => $row['alamat_ktp'],
            'residential_address' => ($row['alamat_rumah'] ?? null) ?: $row['alamat_ktp'],
            'last_education' => $row['pendidikan_terakhir'],
            'university' => $row['perguruan_tinggi'],
            'study_program' => $row['program_studi'],
            'email' => $row['email'],
            'annual_leave_days' => $row['jatah_cuti'] ?? 12,
            'supervisor_id' => $supervisor?->id,
            'mother_name' => $row['nama_ibu'],
            'base_salary' => $row['gaji_pokok'] ?? 0,
            'transport_allowance' => $row['tunjangan_transport'] ?? 0,
            'role' => $row['role'],
            'employment_type' => $row['jenis_kepegawaian'] ?? 'permanent',
            'status' => $row['status'] ?? 'active',
        ];

        $validator = Validator::make($data, (new StoreEmployeeRequest)->rules());

        if ($validator->fails()) {
            throw new RuntimeException("Baris {$line}: ".$validator->errors()->first());
        }

        $saveEmployee->handle($validator->validated());
    }
}
