<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
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

        // request()->validate([
        //     'resume_file' => 'required|file|mimes:pdf,docx|max:20480',
        // ]);

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
                'resume_text' => $request->resume_text,
                'job_description' => $request->job_description,
        ]);

        // print($response);
        // dd($response->json());

        if ($response->failed()) {
            Evaluation::create([
                'workspace_id' => $workspace->id,
                'resume_text' => $request->resume_text,
                'job_description_text' => $request->job_description,
                'status' => EvaluationStatus::Failed,
            ]);

            return redirect()
                ->route('dashboard.workspaces.show', $workspace)
                ->with('evaluation_error', 'Evaluation service could not complete the request.');
        }

        Evaluation::create([
            'workspace_id' => $workspace->id,
            'resume_text' => $request->resume_text,
            'job_description_text' => $request->job_description,
            'status' => EvaluationStatus::Completed,
            'evaluation_data' => $response
        ]);
        
        // $table->id();
        // $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
        // $table->text('resume_text');
        // $table->foreignId('job_listing_id')->nullable()->constrained('job_listings')->nullOnDelete();
        // $table->text('job_description_text')->nullable();
        // $table->string('status')->default(EvaluationStatus::Pending->value);
        // $table->text('failure_reason')->nullable();
        // $table->json('evaluation_data')->nullable();
        // $table->string('evaluator_version')->nullable();
        // $table->timestamps();

        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                'evaluation' => $response->json(),
                'job_description' => request()->job_description,
            ]);
    }
}
