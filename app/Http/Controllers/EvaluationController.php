<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use App\Models\Workspace;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Workspace $workspace)
    {
        // dd($request->resume_text, $request->job_description, $workspace->id);

        $request->validate([
            "resume_text" => ['required'],
        ]);



        // Create evaluation and set status to processing
        $evaluation = Evaluation::create([
            'workspace_id' => $request->workspace->id,
            'resume_text' =>$request->resume_text,
            'job_description_text' => $request->job_description,
            'status' => EvaluationStatus::Processing,
        ]);
        
        EvaluateJob::dispatch(
            $request->resume_text,
            $request->job_description,
            $workspace,
            $evaluation
        );

        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                // 'evaluation' => $response->json(),
                'job_description' => request()->job_description,
            ]);
    }
}
