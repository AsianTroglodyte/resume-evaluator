<?php

use App\Enums\EvaluationStatus;
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
            // It is one or the other when it comes to the workspace_id and submission_id
            // We cannot *easily* enforce this on the DB-side without some hacks outside
            // of the eloquent ORM. we must then enforce app-side.
            $table->foreignId('workspace_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('resume_file_path')->nullable();
            $table->text('resume_text')->nullable();
            $table->foreignId('job_listing_id')->nullable()->constrained('job_listings')->nullOnDelete();
            $table->text('job_description_text')->nullable();
            $table->string('status')->default(EvaluationStatus::Processing->value);
            $table->text('failure_reason')->nullable();
            $table->json('evaluation_data')->nullable();
            $table->string('evaluator_version')->nullable();
            $table->timestamps();
            $table->unique('submission_id');
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
