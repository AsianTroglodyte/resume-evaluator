<?php

namespace App\Http\Controllers;

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Models\Assignment;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr as SupportArr;
class ModuleAssignmentsController extends Controller
{
    //
    public function index(Module $module) {
        $job_listings = $module->jobListings;
        $users = $module->users;

        return view('dashboard.modules.assignments.create', [
            'module' => $module,
            'job_listings' => $job_listings,
            'users' => $users,
        ]);
    }

    public function create(Module $module) {
        $job_listings = $module->jobListings;
        $users = $module->users;

        return view('dashboard.modules.assignments.create', [
            'module' => $module,
            'job_listings' => $job_listings,
            'users' => $users,
        ]);
    }
    
    public function show(Module $module, Assignment $assignment) {
        $users = $module->users;

        return view('dashboard.modules.assignments.show', [
            'module' => $module,
            'assignment' => $assignment,
            'users' => $users,
        ]);
    }

    public function store(Module $module) {
        $validated = request()->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            // made by an actual instructor/admin
            'due_date_enabled' => ['required', 'boolean'],
            'due_at' => ['nullable', 'date', 'after:now'],
            'description' => ['nullable', 'string', 'max:500'],
            'job_listing_source' => ['required', Rule::enum(JobListingSource::class)],
            'module_job_listing_scope' => ['required', Rule::enum(ModuleJobListingScope::class)],
            'assignee_scope' => ['required', Rule::enum(AssigneeScope::class)],
            'allow_resubmission' => ['required', 'boolean'],
            'job_listing_ids' => ['array'],
            'job_listing_ids.*' => [
                'required',
                'integer',
                Rule::exists('job_listings', 'id')->where('module_id', $module->id)],
            'assignee_ids' => ['array'],
            'assignee_ids.*' => [
                'required',
                'integer',
                Rule::exists('module_memberships', 'user_id')->where('module_id', $module->id)],
        ]);
    
        $validated['due_at'] = $validated['due_date_enabled'] ? $validated["due_at"] : null;

        
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
            'created_by_user_id' => 1,
            'module_id' => $module["id"],
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
    
        return redirect()->route('dashboard.modules.assignments.create', ['module' => $module]);
    }

    public function edit(Module $module, Assignment $assignment) {
        $job_listings = $module->jobListings;
        $users = $module->users;

        return view('dashboard.modules.assignments.edit', [
            'module' => $module,
            'job_listings' => $job_listings,
            'assignment' => $assignment,
            'users' => $users,
        ]);
    }

    public function update(Module $module) {
        $job_listings = $module->jobListings;
        $users = $module->users;

        return view('dashboard.modules.assignments.edit', [
            'module' => $module,
            'job_listings' => $job_listings,
            'users' => $users,
        ]);
    }

    public function destroy()
    {
        
    }
}
