<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EvaluationController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Workspace $workspace)
    {
        // if ($workspace->user_id !== request()->user()->id) {
        //     abort(404);
        // }

        request()->validate([
            'resume_file' => 'required|file|mimes:pdf,docx|max:20480',
        ]);

        // dd($workspace->id);

        // $response = Http::baseUrl(config('services.eval.url'))
        //     ->timeout(config('services.eval.timeout'))
        //     ->acceptJson()
        //     ->post('/evaluate', [
        //         'resume_file' => request()->resume_file,
        //         'job_description' => request()->job_description,
        // ]);


        $response = Http::baseUrl(config('services.eval.url'))
            ->timeout(config('services.eval.timeout'))
            ->acceptJson()
            ->post('/evaluate', [
                'resume_file' => request()->resume_file,
                'job_description' => request()->job_description,
        ]);

        // print($response);

        if ($response->failed()) {
            return redirect()
                ->route('dashboard.workspaces.show', $workspace)
                ->with('evaluation_error', 'Evaluation service could not complete the request.');
        }

        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                'evaluation' => $response->json(),
                'job_description' => request()->job_description,
            ]);
    }
}
