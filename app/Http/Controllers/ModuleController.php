<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

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

    public function create() 
    {
        
    }
    
    public function show(Module $module) 
    {
        $job_listings = $module->jobListings;

        $assignments = $module
            ->assignments()
            ->with('assignees', 'jobListings')
            ->get();

        return view('dashboard.modules.show', [
            'job_listings' => $job_listings,
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

    public function destroy()
    {
        dd(request()->all());
    }
}
