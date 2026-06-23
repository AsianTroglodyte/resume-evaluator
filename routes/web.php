<?php
use App\Http\Controllers\ModuleAssignmentsController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleJobListingController;
use App\Http\Controllers\ModuleMembersController;
use App\Http\Controllers\ModuleSettingsController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Route::resource()
Route::get('/', function () {
    return view('home');
});

// Route::get('/login', function () {
//     return view('auth.login');
// })->name('login');

Route::controller(RegisteredUserController::class)->group(function () {
    Route::get('/register', 'create')->name('register');
    Route::post('/register', 'post')->name('register.post');
});

Route::controller(SessionController::class)->group(function () {
    Route::get('/login', 'create')
        ->name('login');
    Route::post('/login', 'store')
        ->name('login.store');
});


Route::controller(ModuleController::class)->group(function () {
    Route::get('/dashboard/modules', 'index')
        ->name('dashboard.modules.index');
    Route::post('/dashboard/modules', 'store')
        ->name('dashboard.modules.store');
    Route::delete('/dashboard/modules/{module}', 'destroy')
        ->name('dashboard.modules.destroy');
    Route::get('/dashboard/modules/{module}', 'show')
        ->name('dashboard.modules.show');
    Route::get('/dashboard/modules/create', 'create')
        ->name('dashboard.modules.create');
});

Route::controller(ModuleMembersController::class)->group(function () {
    Route::get('/dashboard/modules/{module}/members/index', 'index')
        ->name('dashboard.modules.members.index');
    Route::post('/dashboard/modules/{module}/members/index', 'store')
        ->name('dashboard.modules.members.store');
    Route::delete('/dashboard/modules/{module}/members/index', 'destroy')
        ->name('dashboard.modules.members.destroy');  
});

Route::controller(ModuleAssignmentsController::class)->group(function () {
    Route::get('/dashboard/modules/{module}/assignment/create', 'create')
        ->name('dashboard.modules.assignments.create');
    Route::post('/dashboard/modules/{module}/assignment/create', 'store')
        ->name('dashboard.modules.assignments.store');
    Route::get('/dashboard/modules/{module}/assignment/{assignment}', 'show')
        ->scopeBindings()
        ->name('dashboard.modules.assignments.show');
    Route::get('/dashboard/modules/{module}/assignment/{assignment}/edit','edit')
        ->scopeBindings()
        ->name('dashboard.modules.assignments.edit');
    Route::patch('/dashboard/modules/{module}/assignment/{assignment}','update')
        ->name('dashboard.modules.assignments.update');
    Route::delete('/dashboard/modules/{module}/assignment/{assignment}','destroy')
        ->scopeBindings()
        ->name('dashboard.modules.assignments.delete');
});

Route::controller(ModuleJobListingController::class)->group(function () {
    Route::post('/dashboard/modules/{module}/job-listings', 'store')
        ->name('dashboard.modules.job-listings.store');
    Route::patch('/dashboard/modules/{module}/job-listings/{jobListing}', 'update')
        ->scopeBindings()
        ->name('dashboard.modules.job-listings.update');
    Route::delete('/dashboard/modules/{module}/job-listings/{jobListing}', 'destroy')
        ->scopeBindings()
        ->name('dashboard.modules.job-listings.delete');
});

Route::controller(ModuleSettingsController::class)->group(function () {
    Route::get('/dashboard/modules/{module}/settings/index', 'index')
        ->name('dashboard.modules.settings.index');
    Route::patch('/dashboard/modules/{module}/settings/index', 'update')
        ->name('dashboard.modules.settings.index');
});

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
