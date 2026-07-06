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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id');
            $table->foreignId('resume_id')->constrained('resumes')->cascadeOnDelete();
            $table->text('resume_text')->nullable();
            $table->text('job_description_text')->nullable();
            $table->string('status');
            $table->json('evaluation_data')->nullable();
            $table->string('evaluator_version');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
