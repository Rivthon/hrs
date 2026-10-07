<?php

namespace App\Http\Controllers;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\Position;
use App\Models\PublicHoliday;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $employeesOnLeaveToday = $this->employeesOnLeaveToday();

        if (! $user->can('manage-users')) {
            return $this->employeeDashboard($user, $employeesOnLeaveToday);
        }

        $statistics = [
            'employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'lecturers' => Employee::whereHas('user', fn ($query) => $query->where('role', 'dosen'))->count(),
            'staff' => Employee::whereHas('user', fn ($query) => $query->whereIn('role', ['admin', 'hr', 'staff']))->count(),
            'departments' => Department::where('is_active', true)->count(),
            'positions' => Position::where('is_active', true)->count(),
            'pending_supervisor_leave' => LeaveRequest::where('status', LeaveRequestStatus::PendingSupervisor->value)->count(),
            'pending_hr_leave' => LeaveRequest::where('status', LeaveRequestStatus::PendingHr->value)->count(),
            'approved_leave_this_month' => LeaveRequest::query()
                ->where('status', LeaveRequestStatus::Approved->value)
                ->whereMonth('start_date', now()->month)
                ->whereYear('start_date', now()->year)
                ->count(),
        ];

        $recentEmployees = Employee::query()
            ->with(['department', 'position'])
            ->latest()
            ->limit(5)
            ->get();

        $pendingLeaveRequests = LeaveRequest::query()
            ->with(['employee:id,full_name,title_prefix,title_suffix,department_id', 'employee.department:id,name'])
            ->whereIn('status', [LeaveRequestStatus::PendingSupervisor->value, LeaveRequestStatus::PendingHr->value])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        $departmentSummaries = Department::query()
            ->where('is_active', true)
            ->withCount(['employees as active_employees_count' => fn ($query) => $query->where('status', 'active')])
            ->orderByDesc('active_employees_count')
            ->limit(6)
            ->get(['id', 'name']);

        $latestPayrollPeriod = PayrollPeriod::query()
            ->withCount('payrolls')
            ->withSum('payrolls', 'net_salary')
            ->latest('period_date')
            ->first();

        $upcomingHolidays = PublicHoliday::query()
            ->whereDate('holiday_date', '>=', today())
            ->orderBy('holiday_date')
            ->limit(4)
            ->get();

        return view('dashboard', compact(
            'statistics',
            'recentEmployees',
            'pendingLeaveRequests',
            'departmentSummaries',
            'latestPayrollPeriod',
            'upcomingHolidays',
            'employeesOnLeaveToday',
        ));
    }

    /** @param Collection<int, LeaveRequest> $employeesOnLeaveToday */
    private function employeeDashboard(User $user, Collection $employeesOnLeaveToday): View
    {
        $employee = $user->employee;
        abort_unless($employee, 404, 'Data pegawai HRS belum tersedia.');

        $employee->load(['department', 'position', 'supervisor'])
            ->loadSum(['leaveRequests as approved_leave_days' => fn ($query) => $query
                ->where('status', LeaveRequestStatus::Approved->value)
                ->where('leave_type', LeaveType::Annual->value)
                ->whereYear('start_date', now()->year)], 'total_working_days');

        $todos = $employee->todos()
            ->orderBy('is_completed')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->latest('id')
            ->limit(10)
            ->get();
        $workReports = $employee->workReports()->latest('report_date')->latest('id')->limit(5)->get();
        $leaveRequests = $employee->leaveRequests()->latest('submitted_at')->limit(5)->get();
        $pendingBusinessTrips = $employee->businessTrips()
            ->where('status', 'assigned')
            ->orderBy('start_date')
            ->limit(3)
            ->get();
        $bapSummary = $user->role === 'dosen' ? $this->lecturerBapSummary($employee) : null;

        return view('dashboard.employee', compact('employee', 'todos', 'workReports', 'leaveRequests', 'pendingBusinessTrips', 'bapSummary', 'employeesOnLeaveToday'));
    }

    /** @return Collection<int, LeaveRequest> */
    private function employeesOnLeaveToday(): Collection
    {
        return LeaveRequest::query()
            ->with([
                'employee:id,user_id,department_id,position_id,full_name,title_prefix,title_suffix',
                'employee.user:id,role',
                'employee.department:id,name',
                'employee.position:id,name',
            ])
            ->where('status', LeaveRequestStatus::Approved->value)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->where(function ($query): void {
                $query->whereNotIn('leave_type', [LeaveType::HalfDay->value, LeaveType::Hourly->value])
                    ->orWhere(function ($query): void {
                        $query->whereDate('start_date', today())
                            ->whereTime('start_time', '<=', now()->format('H:i:s'))
                            ->whereTime('end_time', '>=', now()->format('H:i:s'));
                    });
            })
            ->whereHas('employee', fn ($query) => $query->where('status', 'active'))
            ->orderBy('end_date')
            ->get();
    }

    /** @return array{connected: bool, academic_year: ?string, theory: int, practice: int, meetings: mixed} */
    private function lecturerBapSummary(Employee $employee): array
    {
        if (! $employee->pas_dosen_id) {
            return ['connected' => false, 'academic_year' => null, 'theory' => 0, 'practice' => 0, 'meetings' => collect()];
        }

        try {
            $academicYear = DB::connection('pas')->table('tahun_ajaran')->where('status_ta', 1)->first();
            $theory = DB::connection('pas')->table('pertemuan as p')
                ->join('jadwal as j', 'j.id', '=', 'p.jadwal_id')
                ->join('kurikulum as k', 'k.kurikulum_id', '=', 'j.kurikulum_id')
                ->join('matakuliah as m', 'm.matakuliah_id', '=', 'k.matakuliah_id')
                ->where('p.dosen_id', $employee->pas_dosen_id)
                ->when($academicYear, fn ($query) => $query->where('j.ta_id', $academicYear->ta_id))
                ->select(['p.tanggal_pertemuan', 'p.topik', 'm.nama as mata_kuliah'])
                ->get()
                ->each(fn ($meeting) => $meeting->jenis = 'Teori');
            $practice = DB::connection('pas')->table('pertemuan_praktik as p')
                ->join('jadwal_praktik as j', 'j.id', '=', 'p.jadwal_praktik_id')
                ->join('kurikulum as k', 'k.kurikulum_id', '=', 'j.kurikulum_id')
                ->join('matakuliah as m', 'm.matakuliah_id', '=', 'k.matakuliah_id')
                ->where('p.dosen_id', $employee->pas_dosen_id)
                ->when($academicYear, fn ($query) => $query->where('j.ta_id', $academicYear->ta_id))
                ->select(['p.tanggal_pertemuan', 'p.topik', 'm.nama as mata_kuliah'])
                ->get()
                ->each(fn ($meeting) => $meeting->jenis = 'Praktik');

            return [
                'connected' => true,
                'academic_year' => $academicYear ? $academicYear->nama.' · '.$academicYear->semester : null,
                'theory' => $theory->count(),
                'practice' => $practice->count(),
                'meetings' => $theory->concat($practice)->sortByDesc('tanggal_pertemuan')->take(5),
            ];
        } catch (Throwable) {
            return ['connected' => false, 'academic_year' => null, 'theory' => 0, 'practice' => 0, 'meetings' => collect()];
        }
    }
}
