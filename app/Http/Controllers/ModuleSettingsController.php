<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

class ModuleSettingsController extends Controller
{
    //
    public function index(Module $module) {
        $module->load('creator');
        // dd($created_by_user);
        return view('dashboard.modules.settings.index', ['module' => $module]);
    }
}
