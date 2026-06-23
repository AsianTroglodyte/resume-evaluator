<?php

namespace App\Http\Controllers;

use App\Enums\GlobalRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    //
    public function create()
    {
        return view('auth.register');
    }

    public function post()
    {
        $validated = request()->validate([
            'first_name' => ['required'],
            'last_name' =>  ['required'],
            'email' =>  ['required', 'email', 'max:254'],
            'password' => ['required',  Password::min(6), 'confirmed'],
        ]);

        $user = User::create([
            ...$validated,
            'global_role' => GlobalRole::User
        ]);

        Auth::login($user);

        return redirect('/dashboard/resumes');
    }
}
