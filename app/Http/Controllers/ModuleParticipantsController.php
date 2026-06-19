<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleParticipantsController extends Controller
{
    //
    public function index(Module $module) {
        $participants = $module
        ->users()
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

        return view('dashboard.modules.participants.index', [
            'module' => $module,
            'participants' => $participants,
        ]);
    }
}
