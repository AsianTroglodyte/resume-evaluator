<?php

namespace App\Jobs;

use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EvaluateJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $resumeFilePath,
        public ?string $jobDescription,
        public Evaluation $evaluation)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $stream = fopen(Storage::disk('local')->path($this->resumeFilePath), 'r');

        try {
            $response = Http::baseUrl(config('services.eval.url'))
                ->timeout(config('services.eval.timeout'))
                ->acceptJson()
                ->attach(
                    'resume_file',
                    $stream,
                    basename($this->resumeFilePath)
                )
                ->post('/evaluate', [
                    'job_description' => $this->jobDescription,
                ]);
        } catch (ConnectionException) {
            $this->evaluation->update([
                'resume_file_path' => $this->resumeFilePath,
                'resume_text' => '',
                'status' => EvaluationStatus::Failed,
                'evaluation_data' => null,
                'failure_reason' => 'Evaluation service seems to be down.',
            ]);

            return;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($response->failed()) {
            $this->evaluation->update([
                'resume_file_path' => $this->resumeFilePath,
                'resume_text' => $response->json('resume_text'),
                'status' => EvaluationStatus::Failed,
                'failure_reason' => 'Evaluation Service was unable to process your request.',
                'evaluation_data' => $response->json(),
            ]);
        } else {
            $this->evaluation->update([
                'resume_file_path' => $this->resumeFilePath,
                'resume_text' => $response->json('resume_text'),
                'status' => EvaluationStatus::Completed,
                'evaluation_data' => $response->json(),
            ]);

            Storage::disk('local')->delete($this->resumeFilePath);
        }
    }
}
