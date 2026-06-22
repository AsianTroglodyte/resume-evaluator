<?php

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Http\Controllers\ModuleAssignmentsController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleJobListingController;
use App\Http\Controllers\ModuleMembersController;
use App\Http\Controllers\ModuleSettingsController;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Arr as SupportArr;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::get('/', function () {return view('home');});


Route::get('/login', function () {return view('auth.login');})->name('login');
Route::get('/register', function () {return view('auth.register');});


Route::get('/dashboard/modules', [ModuleController::class, 'index'])->name('dashboard.modules.index');
Route::get('/dashboard/modules/create', function () {return view('dashboard.modules.create', []);})->name('dashboard.modules.create');


Route::post('/dashboard/modules', [ModuleController::class, 'store'])->name('dashboard.modules.store');

Route::delete('/dashboard/modules/{module}', [ModuleController::class, 'destroy'])->name('dashboard.modules.destroy');
Route::get('/dashboard/modules/{module}', [ModuleController::class, 'show'])->name('dashboard.modules.show');

Route::get('/dashboard/modules/{module}/members/index', [ModuleMembersController::class, 'index'] )->name('dashboard.modules.members.index');
Route::post('/dashboard/modules/{module}/members/index', [ModuleMembersController::class, 'store'] )->name('dashboard.modules.members.store');

Route::get('/dashboard/modules/{module}/assignment/create', [ModuleAssignmentsController::class, 'create'])->name('dashboard.modules.assignments.create');
Route::post('/dashboard/modules/{module}/assignment/create', [ModuleAssignmentsController::class, 'store'])->name('dashboard.modules.assignments.store');

Route::get('/dashboard/modules/{module}/assignment/{assignment}', [ModuleAssignmentsController::class, 'show'])
    ->scopeBindings()
    ->name('dashboard.modules.assignments.show');
Route::get('/dashboard/modules/{module}/assignment/{assignment}/edit', [ModuleAssignmentsController::class, 'edit'])
    ->scopeBindings()
    ->name('dashboard.modules.assignments.edit');
Route::patch('/dashboard/modules/{module}/assignment/{assignment}', [ModuleAssignmentsController::class, 'update'])
    ->scopeBindings()
    ->name('dashboard.modules.assignments.update');
Route::delete('/dashboard/modules/{module}/assignment/{assignment}', [ModuleAssignmentsController::class, 'destroy'])
    ->scopeBindings()
    ->name('dashboard.modules.assignments.delete');

Route::post('/dashboard/modules/{module}/job-listings', [ModuleJobListingController::class, 'store'])->name('dashboard.modules.job-listings.store');
Route::patch('/dashboard/modules/{module}/job-listings/{jobListing}', [ModuleJobListingController::class, 'update'])
    ->scopeBindings()
    ->name('dashboard.modules.job-listings.update');
Route::delete('/dashboard/modules/{module}/job-listings/{jobListing}', [ModuleJobListingController::class, 'destroy'])
    ->scopeBindings()
    ->name('dashboard.modules.job-listings.delete');
Route::delete('/dashboard/modules/{module}/members/index', [ModuleMembersController::class, 'destroy'] )->name('dashboard.modules.members.destroy');
Route::get('/dashboard/modules/{module}/settings/index', [ModuleSettingsController::class, 'index'])->name('dashboard.modules.settings.index');
Route::patch('/dashboard/modules/{module}/settings/index', [ModuleSettingsController::class, 'update'])->name('dashboard.modules.settings.index');


Route::get('/dashboard/resumes', function () {
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'modules' => [
                ['id' => 1, 'name' => 'Module 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'modules' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'modules' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];

    return view('dashboard.resumes.index', [
        'evaluations' => $evaluations,
    ]);
})->name('dashboard.resumes.index');

Route::get('/dashboard/resumes/{id}', function ($id) {
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'modules' => [
                ['id' => 1, 'name' => 'Module 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'modules' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'modules' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];
    $evaluation = collect($evaluations)->firstWhere('id', $id);

    return view('dashboard.resumes.show', [
        'title' => $evaluation['name'],
        'evaluation' => $evaluation['ats_friendliness'],
        'keyword_match' => $evaluation['keyword_match'],
        'modules' => $evaluation['modules'],
    ]);
})->name('dashboard.resumes.show');

Route::get('/testdb', function () {
    $modules = Module::all();
    $users = User::all();
    dd($modules, $users);
});

Route::redirect('/dashboard/admin', '/dashboard/admin/users');

Route::get('/dashboard/admin/users', function () {
    return view('dashboard.admin.users.index', [
        'users' => User::query()->orderBy('last_name', 'asc')->orderBy('first_name', 'asc')->get(),
    ]);
})->name('dashboard.admin.users.index');
