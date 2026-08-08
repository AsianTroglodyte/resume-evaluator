<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Assignment;
use App\Models\Evaluation;
use App\Models\Workspace;
use Illuminate\Http\Request;

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

    // public function storeForSubmission(Request $request, Assignment $assignment)
    // {
    //     $request->validate([
    //         'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
    //     ]);

    //     $resumeFilePath = $request->file('resume_file')->store('resumes/tmp');

    //     // Create evaluation and set status to processing
    //     $evaluation = Evaluation::create([
    //         'submission_id' => null,
    //         'resume_file_path' => $resumeFilePath,
    //         'job_description_text' => $request->job_description,
    //         'status' => EvaluationStatus::Processing,
    //     ]);

    //     EvaluateJob::dispatch(
    //         $resumeFilePath,
    //         $request->job_description,
    //         $workspace,
    //         $evaluation
    //     );

    //     return redirect()
    //         ->route('dashboard.workspaces.show', $workspace)
    //         ->with([
    //             // 'evaluation' => $response->json(),
    //             'job_description' => request()->job_description,
    //         ]);;
    // }

    public function store(Request $request, Workspace $workspace, ?Assignment $assignment)
    {
        // dd($request->resume_text, $request->job_description, $workspace->id);

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
