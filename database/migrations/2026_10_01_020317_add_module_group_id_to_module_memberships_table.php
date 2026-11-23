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
        Schema::table('module_memberships', function (Blueprint $table) {
            $table->foreignId('module_group_id')
                ->nullable()
                ->after('role_in_module')
                ->constrained('module_groups')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_memberships', function (Blueprint $table) {
            $table->dropConstrainedForeignId('module_group_id');
        });
    }
};
