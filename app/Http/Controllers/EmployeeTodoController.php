<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeTodoRequest;
use App\Models\EmployeeTodo;
use Illuminate\Http\RedirectResponse;

class EmployeeTodoController extends Controller
{
    public function store(StoreEmployeeTodoRequest $request): RedirectResponse
    {
        $request->user()->employee->todos()->create($request->validated());

        return back()->with('success', 'Todo berhasil ditambahkan.');
    }

    public function update(EmployeeTodo $employeeTodo): RedirectResponse
    {
        $this->authorizeOwner($employeeTodo);
        $isCompleted = ! $employeeTodo->is_completed;
        $employeeTodo->update([
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        return back()->with('success', 'Status todo berhasil diperbarui.');
    }

    public function destroy(EmployeeTodo $employeeTodo): RedirectResponse
    {
        $this->authorizeOwner($employeeTodo);
        $employeeTodo->delete();

        return back()->with('success', 'Todo berhasil dihapus.');
    }

    private function authorizeOwner(EmployeeTodo $employeeTodo): void
    {
        abort_unless($employeeTodo->employee_id === auth()->user()->employee?->id, 403);
    }
}
