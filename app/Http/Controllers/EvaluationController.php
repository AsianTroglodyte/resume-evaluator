<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Assignment;
use App\Models\Evaluation;
use App\Models\Workspace;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvaluationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function storeForWorkspace(Request $request, Workspace $workspace)
    {
        $request->validate([
            'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $resumeFilePath = $request->file('resume_file')->store('resumes/tmp');

        // Create evaluation and set status to processing
        $evaluation = Evaluation::create([
            'workspace_id' => $workspace->id,
            'resume_file_path' => $resumeFilePath,
            'job_description_text' => $request->job_description,
            'status' => EvaluationStatus::Processing,
        ]);

        // dd("just before dispathing evaluation");
        EvaluateJob::dispatch(
            $resumeFilePath,
            $request->job_description,
            $evaluation
        );

        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                'job_description' => request()->job_description,
            ]);
    }

    public function storeForSubmission(Request $request, Assignment $assignment)
    {
        $request->validate([
            'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $resumeFilePath = $request->file('resume_file')->store('resumes/tmp');

        // dd($request);sub

        $submission = Submission::create([
            'user_id' => $request->user()->id,
            'assignment_id' => $assignment->id,
            'assignment_version' => "1",
            'resubmission_count' => 100000,
            'due_date_snapshot' => null,
        ]);


        // Create evaluation and set status to processing
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

    public function destroyForSubmission(Request $request, Assignment $assignment) {

        // $assignment->submission->evaluation;
        Storage::delete($assignment->submission->evaluation);
        $assignment->submission->delete();

        return redirect()
            ->route('dashboard.modules.assignments.show', [$assignment->module, $assignment])
            ->with([
                'job_description' => request()->job_description,
            ]);
    }

    public function store(Request $request, Workspace $workspace, ?Assignment $assignment)
    {
        $request->validate([
            'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $resumeFilePath = $request->file('resume_file')->store('resumes/tmp');

        // dd($workspace);

        if ($workspace === null and $assignment !== null) {

        } elseif ($workspace !== null and $assignment === null) {

        }

        // Create evaluation and set status to processing
        $evaluation = Evaluation::create([
            'workspace_id' => $workspace->id,
            'submission_id' => null,
            'resume_file_path' => $resumeFilePath,
            'job_description_text' => $request->job_description,
            'status' => EvaluationStatus::Processing,
        ]);

        // dd($path);

        EvaluateJob::dispatch(
            $resumeFilePath,
            $request->job_description,
            $workspace,
            $evaluation
        );

        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                'job_description' => request()->job_description,
            ]);
    }
}
