<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
use App\Enums\JobListingSource;
use App\Jobs\EvaluateJob;
use App\Models\Evaluation;
use App\Models\JobListingClaim;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
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
            'practiceClaims' => $this->practiceClaimsFor($request->user())
                ->with(['assignment.module', 'jobListing'])
                ->get(),
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
            'claim_id' => ['nullable', 'integer'],
        ]);

        $workspace->ensureCanStartEvaluation();

        $claimedJobListing = null;

        if ($request->filled('claim_id')) {
            $claimedJobListing = $this->practiceClaimsFor($request->user())
                ->whereKey($request->integer('claim_id'))
                ->first()
                ?->jobListing;

            if ($claimedJobListing === null) {
                throw ValidationException::withMessages([
                    'claim_id' => 'That claim is no longer available. Choose another job context.',
                ]);
            }
        }

        $jobDescription = $claimedJobListing?->description ?? $request->job_description;

        $resumeFilePath = $request->file('resume_file')->store('resumes/tmp');

        // Create evaluation and set status to processing
        $evaluation = Evaluation::create([
            'workspace_id' => $workspace->id,
            'resume_file_path' => $resumeFilePath,
            'job_listing_id' => $claimedJobListing?->id,
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

        foreach ($stale as $evaluation) {
            if ($evaluation->resume_file_path) {
                Storage::disk('local')->delete($evaluation->resume_file_path);
            }
        }

        $workspace->evaluations()->whereNotIn('id', $keepIds)->delete();

        return redirect()
            ->route('dashboard.workspaces.show', $workspace)
            ->with([
                'job_description' => $claimedJobListing ? null : request()->job_description,
                'claim_id' => $claimedJobListing ? $request->integer('claim_id') : null,
            ]);
    }

    /**
     * The user's current claims on assignments they are still given, for read-only practice JD.
     * Practice never creates, changes, or consumes a claim (ADR 0007).
     *
     * @return Builder<JobListingClaim>
     */
    private function practiceClaimsFor(User $user): Builder
    {
        return JobListingClaim::query()
            ->where('user_id', $user->id)
            ->whereHas('assignment', fn (Builder $assignment) => $assignment
                ->givenTo($user)
                ->where('job_listing_source', '!=', JobListingSource::External->value));
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
