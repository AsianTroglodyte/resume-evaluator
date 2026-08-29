<?php

namespace App\Actions;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;

class RetryEvaluation
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Evaluation $evaluation): void
    {
        
        $evaluation->ensureCanRetry();

        $evaluation->update(['status' => EvaluationStatus::Processing]);

        EvaluateJob::dispatch(
            $evaluation->resume_file_path,
            $evaluation->job_description_text,
            $evaluation
        );
    }
}
