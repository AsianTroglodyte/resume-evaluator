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
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->text('resume_text')->nullable();
            $table->foreignId('job_listing_id')->nullable()->constrained('job_listings')->nullOnDelete();
            $table->text('job_description_text')->nullable();
            $table->string('status')->default(EvaluationStatus::Pending->value);
            $table->text('failure_reason')->nullable();
            $table->json('evaluation_data')->nullable();
            $table->string('evaluator_version')->nullable();
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
