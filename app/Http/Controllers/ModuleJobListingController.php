<?php

namespace App\Http\Controllers;

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
}
