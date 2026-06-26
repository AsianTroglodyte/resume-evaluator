<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\Module;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    //
    public function index()
    {
        // dd(request()->user()->isGlobalAdmin());
        $modules = Module::all();

        return view('dashboard.modules.index', [
            'modules' => $modules,
        ]);
    }

    public function create()
    {

        return view('dashboard.modules.create', []);
    }

    public function show(Module $module)
    {
        $jobListings = $module->jobListings->filter(
            fn (JobListing $jobListing): bool => Gate::allows('view', $jobListing)
        );

        $assignments = $module
            ->assignments()
            ->with('assignees', 'jobListings')
            ->get();

        return view('dashboard.modules.show', [
            'jobListings' => $jobListings,
            'module' => $module,
            'assignments' => $assignments,
        ]);
    }

    public function store()
    {
        request()->validate([
            'name' => ['required', 'min:3'],
        ]);

        Module::create([
            'name' => request('name'),
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()->route('dashboard.modules.index');
    }

    public function destroy(Module $module)
    {
        $module->delete();

        return redirect()->route('dashboard.modules.index');
    }
}
