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
            'email' =>  ['required', 'email', 'max:254'],
            'password' => ['required',  Password::min(6)], 'confirmed',
        ]);

        if (!Auth::attempt($validated)) {
            throw ValidationException::withMessages([
                'email' => 'Sorry, those credential do not match.'
            ]);
        }

        request()->session()->regenerate();

        return redirect()->route('dashboard.resumes.index');

        dd("dieded");
    }

    public function destroy()
    {
        Auth::logout();

        return redirect('/');
    }
}
