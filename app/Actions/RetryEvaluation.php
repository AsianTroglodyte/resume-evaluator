<?php

namespace App\Actions;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;

class RetryEvaluation
{
    public function __invoke(Evaluation $evaluation): void
    {
        $evaluation->update([
            'status' => EvaluationStatus::Processing,
        ]);

        EvaluateJob::dispatch(
            $evaluation->resumeFilePath,
            $evaluation->job_description,
            $evaluation
        );
    }
}
