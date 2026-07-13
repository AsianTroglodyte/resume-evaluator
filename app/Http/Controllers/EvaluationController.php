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
        // dd($request->resume_text, $request->resume_text, $workspace->id);

        EvaluateJob::dispatch(
            $request->resume_text,
            $request->resume_text,
            $workspace
        );

        // $response = Http::baseUrl(config('services.eval.url'))
        //     ->timeout(config('services.eval.timeout'))
        //     ->acceptJson()
        //     ->post('/evaluate', [
        //         'resume_file' => request()->resume_file,
        //         'job_description' => request()->job_description,
        // ]);

        // $response = Http::baseUrl(config('services.eval.url'))
        //     ->timeout(config('services.eval.timeout'))
        //     ->acceptJson()
        //     ->post('/evaluate', [
        //         'resume_text' => $request->resume_text,
        //         'job_description' => $request->job_description,
        // ]);



        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                // 'evaluation' => $response->json(),
                'job_description' => request()->job_description,
            ]);
    }
}
