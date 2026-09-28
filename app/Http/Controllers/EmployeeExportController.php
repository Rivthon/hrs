<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeExportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['NIP', 'Nama Lengkap', 'Email', 'Role', 'Departemen', 'Jabatan', 'Status', 'Tanggal Masuk', 'Masa Kerja'], ';');

            Employee::query()
                ->with(['department', 'position', 'user'])
                ->orderBy('id')
                ->chunk(500, function ($employees) use ($stream): void {
                    foreach ($employees as $employee) {
                        fputcsv($stream, array_map($this->safeCsvValue(...), [
                            $employee->nip,
                            $employee->display_name,
                            $employee->email,
                            $employee->user?->role,
                            $employee->department->name,
                            $employee->position?->name,
                            $employee->status,
                            $employee->joined_on->format('Y-m-d'),
                            $employee->length_of_service,
                        ]), ';');
                    }
                });

            fclose($stream);
        }, 'data-pegawai-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCsvValue(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) === 1 ? "'{$value}" : $value;
    }
}
