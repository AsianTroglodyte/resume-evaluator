<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleJobListingController extends Controller
{
    //
    public function store(Module $module) 
    {
        $validated = request()->validate([
            'name' => ['required', 'string', 'min:3'],
            'description' => ['required', 'string'],
        ]);
    
        $module->jobListings()->create($validated);
    
        return redirect()->route('dashboard.modules.show', ['module' => $module]);
    }

    public function update(Module $module, JobListing $jobListing) 
    {
        $validated = request()->validate([
            'name' => ['required', 'string', 'min:3'],
            'description' => ['required', 'string'],
        ]);


        // dd($validated);

        $jobListing->update([
            'name' => $validated['name'], 
            'description' => $validated['description'] 
        ]);

        return redirect()->route('dashboard.modules.show', ['module' => $module]);
    }
}
