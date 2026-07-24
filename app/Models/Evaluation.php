<?php

namespace App\Models;

use App\Enums\EvaluationStatus;
use Database\Factories\EvaluationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    /** @use HasFactory<EvaluationFactory> */
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'resume_file_path',
        'resume_text',
        'job_listing_id',
        'job_description_text',
        'status',
        'failure_reason',
        'evaluation_data',
        'evaluator_version',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_data' => 'array',
            'status' => EvaluationStatus::class,
        ];
    }
}
