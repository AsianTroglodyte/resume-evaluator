<?php

namespace App\Models;

use App\Enums\EvaluationStatus;
use Database\Factories\EvaluationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Evaluation extends Model
{
    /** @use HasFactory<EvaluationFactory> */
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'submission_id',
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

    /**
     * @throws ValidationException
     */
    public function ensureCanRetry(): void
    {
        if ($this->workspace_id !== null) {
            $this->workspace()->firstOrFail()->ensureCanStartEvaluation();
            return;
        }

        if ($this->submission_id !== null) {
            if ($this->status === EvaluationStatus::Processing) {
                throw ValidationException::withMessages([
                    'evaluation' => 'This evaluation is already processing.',
                ]);
            }
            return;
        }

        throw ValidationException::withMessages([
            'evaluation' => 'This evaluation cannot be retried.',
        ]);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
