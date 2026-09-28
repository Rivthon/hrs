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
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->after('position_id')->constrained('employees')->nullOnDelete();
            $table->string('title_prefix', 50)->nullable()->after('full_name');
            $table->string('title_suffix', 100)->nullable()->after('title_prefix');
            $table->string('gender', 20)->nullable()->after('title_suffix');
            $table->date('date_of_birth')->nullable()->after('joined_on');
            $table->string('nik', 30)->nullable()->unique()->after('date_of_birth');
            $table->string('npwp', 40)->nullable()->unique()->after('nik');
            $table->string('bpjs_health_number', 40)->nullable()->unique()->after('npwp');
            $table->string('bpjs_employment_number', 40)->nullable()->unique()->after('bpjs_health_number');
            $table->string('nidn', 30)->nullable()->unique()->after('bpjs_employment_number');
            $table->string('nip', 30)->nullable()->unique()->after('nidn');
            $table->text('identity_address')->nullable()->after('phone');
            $table->text('residential_address')->nullable()->after('identity_address');
            $table->string('last_education', 50)->nullable()->after('residential_address');
            $table->string('university')->nullable()->after('last_education');
            $table->string('study_program')->nullable()->after('university');
            $table->unsignedSmallInteger('annual_leave_days')->default(12)->after('study_program');
            $table->string('mother_name')->nullable()->after('annual_leave_days');
            $table->decimal('base_salary', 15, 2)->default(0)->after('mother_name');
            $table->decimal('transport_allowance', 15, 2)->default(0)->after('base_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supervisor_id');
            $table->dropUnique(['nik']);
            $table->dropUnique(['npwp']);
            $table->dropUnique(['bpjs_health_number']);
            $table->dropUnique(['bpjs_employment_number']);
            $table->dropUnique(['nidn']);
            $table->dropUnique(['nip']);
            $table->dropColumn([
                'title_prefix', 'title_suffix', 'gender', 'date_of_birth', 'nik', 'npwp',
                'bpjs_health_number', 'bpjs_employment_number', 'nidn', 'nip', 'identity_address',
                'residential_address', 'last_education', 'university', 'study_program',
                'annual_leave_days', 'mother_name', 'base_salary', 'transport_allowance',
            ]);
        });
    }
};
