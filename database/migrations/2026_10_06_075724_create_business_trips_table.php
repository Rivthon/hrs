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
        Schema::create('business_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->constrained('users');
            $table->string('title');
            $table->string('destination');
            $table->text('purpose');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('transportation')->nullable();
            $table->decimal('allowance', 15, 2)->default(0);
            $table->text('assignment_notes')->nullable();
            $table->string('status', 20)->default('assigned');
            $table->timestamp('responded_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('report_summary')->nullable();
            $table->text('report_result')->nullable();
            $table->text('report_notes')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'status', 'start_date']);
            $table->index(['status', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_trips');
    }
};
