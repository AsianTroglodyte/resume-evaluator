<?php

namespace App\Http\Controllers;

use App\Enums\ModuleStatus;
use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleSettingsController extends Controller
{
    //
    public function index(Module $module) {
        $module->load('creator');
        // dd($created_by_user);
        return view('dashboard.modules.settings.index', ['module' => $module]);
    }

    public function update(Module $module) {
        // dd($module);
        
        $validated = request()->validate([
            'name' => ['required', 'min:3'],
            'status' =>  ['required', Rule::enum(ModuleStatus::class)]
        ]);
        
        $module->update([
            'name' => $validated['name'],
            'status' => $validated['status']
        ]);

        $module->load('creator');
        return redirect()->route('dashboard.modules.settings.index', ['module' => $module]); 
    }
}
