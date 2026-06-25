<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    public function profile(): View
    {
        $user = request()->user();

        return view('user.profile', [
            'user' => $user,
        ]);
    }


    public function show(User $user): View
    {
        return view('user.show', [
            'user' => $user,
        ]);
    }
}

// module 1 -> rebeka Abbott -> 13 (instructor here)
// module 2 -> Shaylee Bailey -> 32


