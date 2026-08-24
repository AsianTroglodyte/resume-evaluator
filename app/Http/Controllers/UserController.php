<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rules\Password;

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

    public function updatePassword(User $user) {
        // dd("bruh");
        $validated = request()->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'different:current_password', Password::min(6), 'confirmed'], 
        ]);

        request()->user()->update([
            'password' => $validated["new_password"]
        ]);

        return redirect(route('user.profile'))
            ->with('status', "Password updated.");
    }

    public function updateName() {
        // dd("bruh");
        // dd(request()->all());
        $validated = request()->validate([
            'first_name' => ['required'],
            'last_name' => ['required'], 
        ]);

        request()->user()->update([
            'first_name' => $validated["first_name"],
            'last_name' => $validated["last_name"]
        ]);

        return redirect(route('user.profile'))
            ->with('status', "name updated.");
    }
}

// module 1 -> rebeka Abbott -> 13 (instructor here)
// module 2 -> Shaylee Bailey -> 32


