<?php

namespace App\Http\Controllers;

class SessionController extends Controller
{
    //
    public function index()
    {
        return view('auth.login');
    }
}
