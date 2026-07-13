<?php

namespace App\Jobs;

use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
use App\Models\Workspace;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class EvaluateJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $resumeText,
        public ?string $jobDescription,
        public Workspace $workspace)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create evaluation and set status to processing
        $evaluation = Evaluation::create([
            'workspace_id' => $this->workspace->id,
            'resume_text' =>$this->resumeText,
            'job_description_text' => $this->resumeText,
            'status' => EvaluationStatus::Processing,
        ]);


        $response = Http::baseUrl(config('services.eval.url'))
            ->timeout(config('services.eval.timeout'))
            ->acceptJson()
            ->post('/evaluate', [
                'resume_text' => $this->resumeText,
                'job_description' => $this->jobDescription,
            ]);

        // dd($response->json());
        dump($response->json());


        if ($response->failed()) {
            $evaluation->update([
                'workspace_id' => $this->workspace->id,
                'resume_text' => $this->resumeText,
                'status' => EvaluationStatus::Failed,
            ]);
        } else {
            // dd("about to update");
            $evaluation->update([
                'resume_text' => $this->resumeText,
                'status' => EvaluationStatus::Completed,
                'evaluation_data' => $response->json(),
            ]);
        }
    }
}
