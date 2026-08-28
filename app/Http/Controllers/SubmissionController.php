<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Assignment;
use App\Models\Evaluation;
use App\Models\Module;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SubmissionController extends Controller
{
    public function storeSubmission(Request $request, Module $module, Assignment $assignment)
    {
        $request->validate([
            'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx,txt', 'max:10240'],

        ]);

        if ($assignment->isPastDue()) {
            throw ValidationException::withMessages([
                'submission' => 'The due date for this assignment has passed.',
            ]);
        }

        $resumeFilePath = $request->file('resume_file')->store('resumes/tmp');

        $submission = Submission::create([
            'user_id' => $request->user()->id,
            'assignment_id' => $assignment->id,
            'assignment_version' => '1',
            'due_date_snapshot' => $assignment->due_date,
        ]);

        $evaluation = Evaluation::create([
            'submission_id' => $submission->id,
            'resume_file_path' => $resumeFilePath,
            'job_description_text' => $request->job_description,
            'status' => EvaluationStatus::Processing,
        ]);

        EvaluateJob::dispatch(
            $resumeFilePath,
            $request->job_description,
            $evaluation
        );

        return redirect()
            ->route('dashboard.modules.assignments.show', [$assignment->module, $assignment])
            ->with([
                'job_description' => request()->job_description,
            ]);
    }

    public function destroySubmission(Request $request, Module $module, Assignment $assignment)
    {
        $submission = $assignment->submissionFor($request->user())->first();

        if ($submission === null) {
            return redirect()
                ->route('dashboard.modules.assignments.show', [$assignment->module, $assignment]);
        }

        if ($submission->evaluation?->resume_file_path) {
            Storage::disk('local')->delete($submission->evaluation->resume_file_path);
        }

        $submission->evaluation?->delete();
        $submission->delete();

        return redirect()
            ->route('dashboard.modules.assignments.show', [$assignment->module, $assignment])
            ->with([
                'job_description' => request()->job_description,
            ]);
    }
}
