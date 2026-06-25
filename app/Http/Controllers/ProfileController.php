<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ProfileController extends Controller
{
    public function profile(): View
    {
        return view('user.profile', [
            'user' => auth()->user(),
        ]);
    }
}
