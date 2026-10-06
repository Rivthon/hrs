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
            $table->unsignedBigInteger('pas_dosen_id')->nullable()->unique()->after('user_id');
            $table->string('pas_kode_dosen', 50)->nullable()->unique()->after('pas_dosen_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['pas_dosen_id']);
            $table->dropUnique(['pas_kode_dosen']);
            $table->dropColumn(['pas_dosen_id', 'pas_kode_dosen']);
        });
    }
};
