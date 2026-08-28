<?php

namespace App\Actions;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use Illuminate\Validation\ValidationException;

class RetryEvaluation
{
    public function __invoke(Evaluation $evaluation)
    {
        if ($evaluation->workspace_id !== null) {
            $evaluation->workspace->ensureCanStartEvaluation();
            $evaluation->update(['status' => EvaluationStatus::Processing]); 
            return EvaluateJob::dispatch(
                $evaluation->resume_file_path,
                $evaluation->job_description,
                $evaluation
            );
        }

        if ($evaluation->submission_id !== null) {
            // Assignment: one eval per submission; only block if already processing
            if ($evaluation->status === EvaluationStatus::Processing) {
                throw ValidationException::withMessages([
                    'evaluation' => 'This evaluation is already processing.',
                ]);
            }
            $evaluation->update(['status' => EvaluationStatus::Processing]); 
            return EvaluateJob::dispatch(
                $evaluation->resume_file_path,
                $evaluation->job_description,
                $evaluation
            );
        }

        throw ValidationException::withMessages([
            'evaluation' => 'This evaluation cannot be retried.',
        ]);
    }
}
