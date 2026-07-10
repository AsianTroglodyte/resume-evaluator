<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    //
    protected $fillable = [
        'workspace_id',
        'resume_text',
        'job_listing_id',
        'job_description_text',
        'status',
        'failure_reason',
        'evaluation_data',
        'evaluator_version'
    ];

    protected function casts(): array
    {
        return [
            'evaluation_data' => 'array',
        ];
    }

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
