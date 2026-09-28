<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $statistics = [
            'employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'departments' => Department::where('is_active', true)->count(),
            'positions' => Position::where('is_active', true)->count(),
        ];

        $recentEmployees = Employee::query()
            ->with(['department', 'position'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('statistics', 'recentEmployees'));
    }
}
