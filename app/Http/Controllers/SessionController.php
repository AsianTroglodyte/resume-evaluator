<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rules\Password;

class SessionController extends Controller
{
    //
    public function create()
    {
        return view('auth.login');
    }

    public function store()
    {
        request()->validate([
            'first_name' => ['required'],
            'last_name' =>  ['required'],
            'email' =>  ['required', 'email', 'max:254', 'confirmed'],
            'password' => ['required',  Password::min(6)], 'confirmed',
        ]);

        dd(request()->all());
    }
}
