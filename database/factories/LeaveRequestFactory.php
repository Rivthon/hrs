<?php

namespace Database\Factories;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveType;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'replacement_employee_id' => Employee::factory(),
            'direct_supervisor_id' => Employee::factory(),
            'leave_type' => LeaveType::Annual,
            'start_date' => now()->addWeek()->startOfWeek(),
            'end_date' => now()->addWeek()->startOfWeek()->addDays(2),
            'total_working_days' => 3,
            'reason' => fake()->sentence(),
            'status' => LeaveRequestStatus::PendingSupervisor,
            'submitted_at' => now(),
        ];
    }
}
