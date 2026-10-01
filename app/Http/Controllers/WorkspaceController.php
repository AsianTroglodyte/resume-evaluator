<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use App\Models\Workspace;
use App\Support\PracticeJobContexts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function index(Request $request): View
    {
        $workspaces = $request->user()
            ->workspaces()
            ->latest('updated_at')
            ->get();

        return view('dashboard.workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    }

    public function show(Request $request, Workspace $workspace): View
    {
        return view('dashboard.workspaces.show', [
            'workspace' => $workspace,
            'practiceJobContexts' => (new PracticeJobContexts)($request->user()),
        ]);
    }

    public function showEvaluation(Workspace $workspace, Evaluation $evaluation): View
    {
        $evaluation->load('jobListing');

        return view('dashboard.workspaces.evaluations.show', [
            'workspace' => $workspace,
            'evaluation' => $evaluation,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'workspace_name' => ['required', 'min:3'],
        ]);

        $request->user()->workspaces()->create([
            'name' => $request->workspace_name,
        ]);

        return redirect()->route('dashboard.workspaces.index');
    }

    public function storeEvaluation(Request $request, Workspace $workspace)
    {
        $request->validate([
            'resume_file' => ['required', 'file', 'mimes:pdf,doc,docx,txt', 'max:10240'],
            'job_listing_id' => ['nullable', 'integer'],
        ]);

        $workspace->ensureCanStartEvaluation();

        $practiceJobListing = null;

        if ($request->filled('job_listing_id')) {
            $practiceJobListing = (new PracticeJobContexts)->allows($request->user(), $request->integer('job_listing_id'));

            if ($practiceJobListing === null) {
                throw ValidationException::withMessages([
                    'job_listing_id' => 'That job listing is no longer available. Choose another job context.',
                ]);
            }
        }

        $jobDescription = $practiceJobListing?->description ?? $request->job_description;

        $resumeFilePath = $request->file('resume_file')->store('resumes/tmp');

        // Create evaluation and set status to processing
        $evaluation = Evaluation::create([
            'workspace_id' => $workspace->id,
            'resume_file_path' => $resumeFilePath,
            'job_listing_id' => $practiceJobListing?->id,
            'job_description_text' => $jobDescription,
            'status' => EvaluationStatus::Processing,
        ]);

        // Delete any evaluation files past 5
        EvaluateJob::dispatch(
            $resumeFilePath,
            $jobDescription,
            $evaluation
        );

        $keepIds = $workspace->latestEvaluations()
            ->latest('id')
            ->limit(5)
            ->pluck('id');

        $stale = $workspace->evaluations()
            ->whereNotIn('id', $keepIds)
            ->get(['id', 'resume_file_path']);

        foreach ($stale as $staleEvaluation) {
            if ($staleEvaluation->resume_file_path) {
                Storage::disk('local')->delete($staleEvaluation->resume_file_path);
            }
        }

        $workspace->evaluations()->whereNotIn('id', $keepIds)->delete();

        return redirect()
            ->route('dashboard.workspaces.evaluations.show', [$workspace, $evaluation])
            ->with([
                'job_description' => $practiceJobListing ? null : request()->job_description,
                'job_listing_id' => $practiceJobListing?->id,
            ]);
    }

    public function destroy(Workspace $workspace): RedirectResponse
    {
        foreach ($workspace->evaluations as $workspaceEvaluation) {
            Storage::disk('local')->delete($workspaceEvaluation->resume_file_path);
            $workspaceEvaluation->delete();
        }
        $workspace->delete();

        return redirect()->route('dashboard.workspaces.index');
    }

    public function update(Workspace $workspace): RedirectResponse
    {
        // dd(request()->new_workspace_name);
        $validated = request()->validate([
            'workspace_name' => ['required', 'min:3'],
        ]);

        $workspace->update([
            'name' => $validated['workspace_name'],
        ]);

        return redirect()->route('dashboard.workspaces.show', $workspace);
    }
}
