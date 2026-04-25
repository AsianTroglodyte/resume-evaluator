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
        Schema::create('resume_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained('resumes')->cascadeOnDelete();
            // $table->foreignId("evaluated_by_user_id");
            $table->text('job_description_text')->nullable();
            $table->decimal('ats_score', 5, 2);
            // $table->numeric("keyword_score");
            // $table->json("matched_keywords_json");
            // $table->json("missing_keywords_json");
            // $table->string("feedback_blob");
            $table->string('evaluator_version');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_scans');
    }
};
