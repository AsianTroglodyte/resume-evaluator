<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function() {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/dashboard/evaluations', function() {
    return view('dashboard.evaluations');
});

Route::get('/dashboard/groups', function() {
    return view('dashboard.groups');
});

// Route::get('/about', function() {
//     ;return view('about')
// });

// Route::get('/contact', function() {
//     return view('contact');
// });

