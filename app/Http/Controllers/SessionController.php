<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    //
    public function create()
    {
        return view('auth.login');
    }

    public function store()
    {
        $validated = request()->validate([
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required',  Password::min(6)],
        ]);

        if (!Auth::attempt($validated)) {
            throw ValidationException::withMessages([
                'email' => 'Sorry, those credentials do not match.'
            ]);
        }

        request()->session()->regenerate();

        return redirect()->route('dashboard.workspaces.index');
    }

    public function destroy()
    {
        Auth::logout();

        return redirect()->route('home');
    }
}
