<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('position_allowance', 15, 2)->default(0);
            $table->decimal('functional_allowance', 15, 2)->default(0);
            $table->decimal('teaching_honor', 15, 2)->default(0);
            $table->decimal('proctoring_honor', 15, 2)->default(0);
            $table->decimal('final_seminar_honor', 15, 2)->default(0);
            $table->decimal('thesis_defense_honor', 15, 2)->default(0);
            $table->decimal('thesis_supervisor_honor', 15, 2)->default(0);
            $table->decimal('pkk_supervision_honor', 15, 2)->default(0);
            $table->decimal('practical_exam_honor', 15, 2)->default(0);
            $table->decimal('duty_honor', 15, 2)->default(0);
            $table->decimal('bpjs_employment_deduction', 15, 2)->default(0);
            $table->decimal('bpjs_health_deduction', 15, 2)->default(0);
            $table->decimal('income_tax_deduction', 15, 2)->default(0);
            $table->decimal('transport_deduction', 15, 2)->default(0);
            $table->decimal('lateness_deduction', 15, 2)->default(0);
            $table->decimal('gross_income', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_period_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
