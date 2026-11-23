<?php

namespace App\Http\Controllers;

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Enums\RoleInModule;
use App\Models\Assignment;
use App\Models\JobListingClaim;
use App\Models\Module;
use App\Models\Submission;
use App\Models\User;
use App\Support\ReleaseInaccessibleClaims;
use Illuminate\Http\Request;
use Illuminate\Support\Arr as SupportArr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ModuleAssignmentsController extends Controller
{
    public function create(Module $module)
    {
        $job_listings = $module->jobListings;
        $assignableMembers = $module->assignableMembers;

        return view('dashboard.modules.assignments.create', [
            'module' => $module,
            'job_listings' => $job_listings,
            'assignableMembers' => $assignableMembers,
            'groups' => $module->groups()->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, Module $module, Assignment $assignment)
    {
        $users = $module->members;
        $assignment->load(['assignees', 'jobListings', 'group']);

        $viewData = [
            'module' => $module,
            'assignment' => $assignment,
            'users' => $users,
            'claimableJobListings' => $assignment->claimableJobListings()->get(),
            'currentClaim' => $assignment->claimFor($request->user())->first(),
        ];

        if ($request->user()->can('seeAllAssignmentDetails', $assignment)) {
            $viewData['submissionRows'] = $this->submissionRowsFor($module, $assignment);
        }

        return view('dashboard.modules.assignments.show', $viewData);
    }

    public function store(Module $module)
    {
        $validated = request()->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            // made by an actual instructor/admin
            'due_date_enabled' => ['required', 'boolean'],
            'due_date' => ['nullable', 'date',
                Rule::requiredIf(fn () => request()->boolean('due_date_enabled'))],
            'description' => ['nullable', 'string', 'max:500'],
            'job_listing_source' => ['required',
                Rule::enum(JobListingSource::class)],
            'module_job_listing_scope' => ['required',
                Rule::enum(ModuleJobListingScope::class)],
            'assignee_scope' => ['required',
                Rule::enum(AssigneeScope::class)],
            'module_group_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => request('assignee_scope') === AssigneeScope::Group->value),
                Rule::exists('module_groups', 'id')->where('module_id', $module->id)],
            'job_listing_ids' => ['array',
                Rule::requiredIf(fn () => request('module_job_listing_scope') === ModuleJobListingScope::Selected->value)],
            'job_listing_ids.*' => [
                'required',
                'integer',
                Rule::exists('job_listings', 'id')
                    ->where('module_id', $module->id)],
            'job_listing_capacities' => ['array'],
            'job_listing_capacities.*' => ['nullable', 'integer', 'min:1'],
            'assignee_ids' => ['array'],
            'assignee_ids.*' => [
                'required',
                'integer',
                Rule::exists('module_memberships', 'user_id')
                    ->where('module_id', $module->id)
                    ->where('status', 'active')
                    ->where('role_in_module', RoleInModule::Student)],
        ]);

        $validated['due_date'] = $validated['due_date_enabled'] ? $validated['due_date'] : null;

        $assignmentInfo = SupportArr::only($validated, [
            'title',
            'due_date',
            'description',
            'job_listing_source',
            'module_job_listing_scope',
            'assignee_scope',
        ]);
        $assigneeScope = AssigneeScope::from($assignmentInfo['assignee_scope']);
        $assignment = $module->assignments()->create([
            // ...$validated,
            'created_by_user_id' => auth()->id(),
            'module_id' => $module['id'],
            'title' => $assignmentInfo['title'],
            'description' => $assignmentInfo['description'],
            'due_date' => $assignmentInfo['due_date'],
            'assignee_scope' => $assigneeScope,
            'module_group_id' => $assigneeScope === AssigneeScope::Group ? $validated['module_group_id'] : null,
            'job_listing_source' => JobListingSource::from($assignmentInfo['job_listing_source']),
            'module_job_listing_scope' => ModuleJobListingScope::from($assignmentInfo['module_job_listing_scope']),
            'allow_resubmission' => false,
        ]);

        $assignment->jobListings()->sync($this->allowedJobListingsWithCapacity($validated));

        $assigneeIds = $assigneeScope === AssigneeScope::Selected ? ($validated['assignee_ids'] ?? []) : [];
        foreach ($assigneeIds as $assigneeId) {
            $assignment->assignmentAssignees()->create([
                'user_id' => $assigneeId,
                'assignment_id' => $assignment['id'],
            ]);
        }

        return redirect()->route('dashboard.modules.show', ['module' => $module]);
    }

    public function edit(Module $module, Assignment $assignment)
    {
        $job_listings = $module->jobListings;
        $users = $module->members;
        $assignment->load(['assignees', 'jobListings']);

        return view('dashboard.modules.assignments.edit', [
            'module' => $module,
            'job_listings' => $job_listings,
            'assignment' => $assignment,
            'users' => $users,
            'assignableMembers' => $module->assignableMembers,
            'groups' => $module->groups()->orderBy('name')->get(),
        ]);
    }

    public function update(Module $module, Assignment $assignment)
    {
        $validated = request()->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            // made by an actual instructor/admin
            'due_date_enabled' => ['required', 'boolean'],
            'due_date' => ['nullable', 'date',
                Rule::requiredIf(fn () => request()->boolean('due_date_enabled'))],
            'description' => ['nullable', 'string', 'max:500'],
            'job_listing_source' => ['required', Rule::enum(JobListingSource::class)],
            'module_job_listing_scope' => ['required', Rule::enum(ModuleJobListingScope::class)],
            'assignee_scope' => ['required', Rule::enum(AssigneeScope::class)],
            'module_group_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => request('assignee_scope') === AssigneeScope::Group->value),
                Rule::exists('module_groups', 'id')->where('module_id', $module->id)],
            'allow_resubmission' => ['required', 'boolean'],
            'job_listing_ids' => ['array',
                Rule::requiredIf(fn () => request('module_job_listing_scope') === ModuleJobListingScope::Selected->value)],
            'job_listing_ids.*' => [
                'required',
                'integer',
                Rule::exists('job_listings', 'id')
                    ->where('module_id', $module->id)],
            'job_listing_capacities' => ['array'],
            'job_listing_capacities.*' => ['nullable', 'integer', 'min:1'],
            'assignee_ids' => ['array'],
            'assignee_ids.*' => [
                'required',
                'integer',
                Rule::exists('module_memberships', 'user_id')
                    ->where('module_id', $module->id)
                    ->where('status', 'active')
                    ->where('role_in_module', RoleInModule::Student)],
        ]);

        $validated['due_date'] = $validated['due_date_enabled'] ? $validated['due_date'] : null;

        $assignmentInfo = SupportArr::only($validated, [
            'title',
            'due_date',
            'description',
            'job_listing_source',
            'module_job_listing_scope',
            'assignee_scope',
        ]);
        $assigneeScope = AssigneeScope::from($assignmentInfo['assignee_scope']);

        $assignment->update([
            // ...$validated,
            'module_id' => $module['id'],
            'title' => $assignmentInfo['title'],
            'description' => $assignmentInfo['description'],
            'due_date' => $assignmentInfo['due_date'],
            'assignee_scope' => $assigneeScope,
            'module_group_id' => $assigneeScope === AssigneeScope::Group ? $validated['module_group_id'] : null,
            'job_listing_source' => JobListingSource::from($assignmentInfo['job_listing_source']),
            'module_job_listing_scope' => ModuleJobListingScope::from($assignmentInfo['module_job_listing_scope']),
            'allow_resubmission' => false,
        ]);

        // dd($assignment);

        $assignment->jobListings()->sync($this->allowedJobListingsWithCapacity($validated));

        $assigneeIds = $assigneeScope === AssigneeScope::Selected ? ($validated['assignee_ids'] ?? []) : [];
        $assignment->allAssignees()->sync($assigneeIds);

        $assignment->claims()
            ->whereNotIn('job_listing_id', $assignment->claimableJobListings()->pluck('job_listings.id'))
            ->delete();
        (new ReleaseInaccessibleClaims)($module->id, $assignment->claims()->pluck('user_id')->all());

        $users = $module->members;

        return redirect()->route('dashboard.modules.assignments.show', [
            'module' => $module,
            'assignment' => $assignment,
            'users' => $users,
        ]);
    }

    public function destroy(Module $module, Assignment $assignment)
    {
        // $assignment->
        $submissionResumeFilePaths = $assignment->allEvaluations()
            ->get()
            ->pluck('resume_file_path');

        foreach ($submissionResumeFilePaths as $submissionResumeFilePath) {
            Storage::disk('local')->delete($submissionResumeFilePath);
        }

        $assignment->delete();

        return redirect()->route('dashboard.modules.show', [
            'module' => $module,
        ]);
    }

    /**
     * Build one row per assignee, pairing each with their submission
     * (and its evaluation) when one exists, for the instructor grading view.
     *
     * @return Collection<int, array{user: User, submission: ?Submission, claim: ?JobListingClaim}>
     */
    private function submissionRowsFor(Module $module, Assignment $assignment): Collection
    {
        $roster = $assignment->roster()->get();

        $claimsByUserId = $assignment->claims()
            ->with('jobListing:id,name')
            ->whereIn('user_id', $roster->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $submissionsByUserId = $assignment->submissions()
            ->with('evaluation')
            ->whereIn('user_id', $roster->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return $roster
            ->sortBy(fn ($user) => strtolower($user->last_name.' '.$user->first_name))
            ->values()
            ->map(fn ($user) => [
                'user' => $user,
                'submission' => $submissionsByUserId->get($user->id),
                'claim' => $claimsByUserId->get($user->id),
            ]);
    }

    /**
     * Pivot rows for `sync()`, carrying each selected listing's capacity (blank = unlimited).
     *
     * @param  array<string, mixed>  $validated
     * @return array<int, array{capacity: ?int}>
     */
    private function allowedJobListingsWithCapacity(array $validated): array
    {
        $capacities = $validated['job_listing_capacities'] ?? [];

        return collect($validated['job_listing_ids'] ?? [])
            ->mapWithKeys(fn (int|string $jobListingId) => [
                (int) $jobListingId => ['capacity' => $capacities[$jobListingId] ?? null],
            ])
            ->all();
    }
}
