<?php

namespace App\Http\Controllers;

use App\Enums\GlobalRole;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    //
    public function create()
    {
        return view('auth.register');
    }

    public function post(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'max:254', Rule::unique('users', 'email')],
            'password' => ['required',  Password::min(6), 'confirmed'],
        ]);

        $user = User::create([
            ...$validated,
            'global_role' => GlobalRole::User,
        ]);

        Auth::login($user);
        
        $user->sendEmailVerificationNotification();
 
        return redirect()->route('verification.notice');
    }

    public function verify (EmailVerificationRequest $request)
    {
        $request->fulfill();
 
        return redirect('/home');
    }
}
