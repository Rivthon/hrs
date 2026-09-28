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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('replacement_employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('direct_supervisor_id')->constrained('employees')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('total_working_days');
            $table->text('reason');
            $table->string('status', 30)->default('pending_supervisor');
            $table->timestamp('submitted_at');
            $table->foreignId('supervisor_approved_by_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('supervisor_approved_at')->nullable();
            $table->text('supervisor_notes')->nullable();
            $table->foreignId('hr_approved_by_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('hr_approved_at')->nullable();
            $table->text('hr_notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['direct_supervisor_id', 'status']);
            $table->index(['status', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
