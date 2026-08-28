<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function show(Workspace $workspace): View
    {
        return view('dashboard.workspaces.show', [
            'workspace' => $workspace,
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
        ]);

        $workspace->ensureCanStartEvaluation();

        $resumeFilePath = $request->file('resume_file')->store('resumes/tmp');

        // Create evaluation and set status to processing
        $evaluation = Evaluation::create([
            'workspace_id' => $workspace->id,
            'resume_file_path' => $resumeFilePath,
            'job_description_text' => $request->job_description,
            'status' => EvaluationStatus::Processing,
        ]);

        // Delete any evaluation files past 5
        EvaluateJob::dispatch(
            $resumeFilePath,
            $request->job_description,
            $evaluation
        );

        $keepIds = $workspace->latestEvaluations()
            ->latest('id')
            ->limit(5)
            ->pluck('id');

        $stale = $workspace->evaluations()
            ->whereNotIn('id', $keepIds)
            ->get(['id', 'resume_file_path']);

        foreach ($stale as $evaluation) {
            if ($evaluation->resume_file_path) {
                Storage::disk('local')->delete($evaluation->resume_file_path);
            }
        }

        $workspace->evaluations()->whereNotIn('id', $keepIds)->delete();

        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                'job_description' => request()->job_description,
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
