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
        Schema::create('employee_todos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'is_completed', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_todos');
    }
};
