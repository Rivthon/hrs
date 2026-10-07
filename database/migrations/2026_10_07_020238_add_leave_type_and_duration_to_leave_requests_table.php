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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('leave_type', 30)->default('annual')->after('direct_supervisor_id');
            $table->time('start_time')->nullable()->after('end_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('end_time');
            $table->string('supporting_document_path')->nullable()->after('reason');
            $table->index(['leave_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['leave_type', 'status']);
            $table->dropColumn(['leave_type', 'start_time', 'end_time', 'duration_minutes', 'supporting_document_path']);
        });
    }
};
