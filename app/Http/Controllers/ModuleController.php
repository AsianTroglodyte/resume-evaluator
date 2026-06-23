<?php

namespace App\Http\Controllers;

use App\Models\Module;

class ModuleController extends Controller
{
    //
    public function index()
    {
        $modules = Module::all();

        return view('dashboard.modules.index', [
            'modules' => $modules,
        ]);
    }

    public function create() {
        return view('dashboard.modules.create', []);
    }

    public function show(Module $module)
    {
        $jobListings = $module->jobListings;

        $assignments = $module
            ->assignments()
            ->with('activeAssignees', 'jobListings')
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
            'created_by_user_id' => 1,
        ]);

        return redirect()->route('dashboard.modules.index');
    }

    public function destroy(Module $module)
    {
        $module->delete();

        return redirect()->route('dashboard.modules.index');
    }
}
