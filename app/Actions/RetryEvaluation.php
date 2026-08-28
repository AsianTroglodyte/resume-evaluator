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

        dd($evaluation->status);

        EvaluateJob::dispatch(
            $evaluation->resume_file_path,
            $evaluation->job_description,
            $evaluation
        );
    }
}
