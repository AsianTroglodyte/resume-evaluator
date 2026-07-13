<?php

namespace App\Http\Controllers;

use App\Jobs\EvaluateJob;
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

        EvaluateJob::dispatch(
            $request->resume_text,
            $request->job_description,
            $workspace
        );

        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                // 'evaluation' => $response->json(),
                'job_description' => request()->job_description,
            ]);
    }
}
