<?php

namespace App\Models;

use Database\Factories\EmployeeTodoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'title', 'due_date', 'priority', 'is_completed', 'completed_at'])]
class EmployeeTodo extends Model
{
    /** @use HasFactory<EmployeeTodoFactory> */
    use HasFactory;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['due_date' => 'date', 'is_completed' => 'boolean', 'completed_at' => 'datetime'];
    }
}
