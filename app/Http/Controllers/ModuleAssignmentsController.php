<?php

namespace App\Http\Controllers;

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Models\Assignment;
use App\Models\Module;
use Illuminate\Support\Arr as SupportArr;
use Illuminate\Validation\Rule;

class ModuleAssignmentsController extends Controller
{
    public function create(Module $module)
    {
        $job_listings = $module->jobListings;
        $users = $module->users;

        return view('dashboard.modules.assignments.create', [
            'module' => $module,
            'job_listings' => $job_listings,
            'users' => $users,
        ]);
    }

    public function show(Module $module, Assignment $assignment)
    {
        $users = $module->users;
        $assignment->load(['assignees', 'jobListings']);

        return view('dashboard.modules.assignments.show', [
            'module' => $module,
            'assignment' => $assignment,
            'users' => $users,
        ]);
    }

    public function store(Module $module)
    {

        // dd(request()->due_date_enabled);
        $validated = request()->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            // made by an actual instructor/admin
            'due_date_enabled' => ['required', 'boolean'],
            'due_at' => ['nullable', 'date', 
                Rule::requiredIf(fn () => request()->boolean('due_date_enabled'))],
            'description' => ['nullable', 'string', 'max:500'],
            'job_listing_source' => ['required',
                Rule::enum(JobListingSource::class)],
            'module_job_listing_scope' => ['required',
                Rule::enum(ModuleJobListingScope::class)],
            'assignee_scope' => ['required',
                Rule::enum(AssigneeScope::class)],
            'allow_resubmission' => ['required', 'boolean'],
            'job_listing_ids' => ['array',
                Rule::requiredIf(fn () => 
                request('module_job_listing_scope') === ModuleJobListingScope::Selected->value)],
            'job_listing_ids.*' => [
                'required',
                'integer',
                Rule::exists('job_listings', 'id')
                    ->where('module_id', $module->id)],
            'assignee_ids' => ['array',
                Rule::requiredIf(fn () => 
                request('assignee_scope') === AssigneeScope::Selected->value)],
            'assignee_ids.*' => [
                'required',
                'integer',
                Rule::exists('module_memberships', 'user_id')
                    ->where('module_id', $module->id)
                    ->where('status', 'active')],
        ]);

        $validated['due_at'] = $validated['due_date_enabled'] ? $validated['due_at'] : null;

        $assignmentInfo = SupportArr::only($validated, [
            'title',
            'due_at',
            'description',
            'job_listing_source',
            'module_job_listing_scope',
            'assignee_scope',
            'allow_resubmission',
        ]);
        $assignment = $module->assignments()->create([
            // ...$validated,
            'created_by_user_id' => auth()->id(),
            'module_id' => $module['id'],
            'title' => $assignmentInfo['title'],
            'description' => $assignmentInfo['description'],
            'due_at' => $assignmentInfo['due_at'],
            'assignee_scope' => AssigneeScope::from($assignmentInfo['assignee_scope']),
            'job_listing_source' => JobListingSource::from($assignmentInfo['job_listing_source']),
            'module_job_listing_scope' => ModuleJobListingScope::from($assignmentInfo['module_job_listing_scope']),
            'allow_resubmission' => $assignmentInfo['allow_resubmission'],
        ]);

        $jobListingIds = $validated['job_listing_ids'] ?? [];
        foreach ($jobListingIds as $jobListingId) {
            $assignment->assignmentAllowedJobListings()->create([
                'job_listing_id' => $jobListingId,
                'assignment_id' => $assignment['id'],
            ]);
        }

        $assigneeIds = $validated['assignee_ids'] ?? [];
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
        $users = $module->users;
        $assignment->load(['assignees', 'jobListings']);

        return view('dashboard.modules.assignments.edit', [
            'module' => $module,
            'job_listings' => $job_listings,
            'assignment' => $assignment,
            'users' => $users,
        ]);
    }

    public function update(Module $module, Assignment $assignment)
    {

        $validated = request()->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            // made by an actual instructor/admin
            'due_date_enabled' => ['required', 'boolean'],
            'due_at' => ['nullable', 'date', 
                Rule::requiredIf(fn () => request()->boolean('due_date_enabled'))],
            'description' => ['nullable', 'string', 'max:500'],
            'job_listing_source' => ['required', Rule::enum(JobListingSource::class)],
            'module_job_listing_scope' => ['required', Rule::enum(ModuleJobListingScope::class)],
            'assignee_scope' => ['required', Rule::enum(AssigneeScope::class)],
            'allow_resubmission' => ['required', 'boolean'],
            'job_listing_ids' => ['array',
                Rule::requiredIf(fn () => 
                request('module_job_listing_scope') === ModuleJobListingScope::Selected->value)],
            'job_listing_ids.*' => [
                'required',
                'integer',
                Rule::exists('job_listings', 'id')
                    ->where('module_id', $module->id)],
            'assignee_ids' => ['array',
                Rule::requiredIf(fn () => 
                request('assignee_scope') === AssigneeScope::Selected->value)],
            'assignee_ids.*' => [
                'required',
                'integer',
                Rule::exists('module_memberships', 'user_id')
                    ->where('module_id', $module->id)
                    ->where('status', 'active')],
        ]);

        $validated['due_at'] = $validated['due_date_enabled'] ? $validated['due_at'] : null;

        $assignmentInfo = SupportArr::only($validated, [
            'title',
            'due_at',
            'description',
            'job_listing_source',
            'module_job_listing_scope',
            'assignee_scope',
            'allow_resubmission',
        ]);

        $assignment->update([
            // ...$validated,
            'module_id' => $module['id'],
            'title' => $assignmentInfo['title'],
            'description' => $assignmentInfo['description'],
            'due_at' => $assignmentInfo['due_at'],
            'assignee_scope' => AssigneeScope::from($assignmentInfo['assignee_scope']),
            'job_listing_source' => JobListingSource::from($assignmentInfo['job_listing_source']),
            'module_job_listing_scope' => ModuleJobListingScope::from($assignmentInfo['module_job_listing_scope']),
            'allow_resubmission' => $assignmentInfo['allow_resubmission'],
        ]);

        // dd($assignment);

        $jobListingIds = $validated['job_listing_ids'] ?? [];
        $assignment->jobListings()->sync($jobListingIds);

        $assigneeIds = $validated['assignee_ids'] ?? [];
        $assignment->allAssignees()->sync($assigneeIds);

        $users = $module->users;

        return redirect()->route('dashboard.modules.assignments.show', [
            'module' => $module,
            'assignment' => $assignment,
            'users' => $users,
        ]);
    }

    public function destroy(Module $module, Assignment $assignment)
    {
        $assignment->delete();

        return redirect()->route('dashboard.modules.show', [
            'module' => $module,
        ]);
    }
}
