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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules');
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->string('title');
            $table->text('description');
            $table->timestamp('due_at')->nullable();
            $table->string('assignee_scope');
            $table->string('job_listing_source');
            $table->string('module_job_listing_scope')->nullable();
            $table->boolean('allow_resubmission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
