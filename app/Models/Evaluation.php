<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    //
    // protected $fillable = [
    //     'bruh' => 'bruh'
    // ];



    // ('evaluations', function (Blueprint $table) {
    //     $table->id();
    //     $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
    //     $table->text('resume_text');
    //     $table->foreignId('job_listing_id')->nullable()->constrained('job_listings')->nullOnDelete();
    //     $table->text('job_description_text')->nullable();
    //     $table->string('status')->default(EvaluationStatus::Pending->value);
    //     $table->text('failure_reason')->nullable();
    //     $table->json('evaluation_data')->nullable();
    //     $table->string('evaluator_version')->nullable();
    //     $table->timestamps();
    // });
}
