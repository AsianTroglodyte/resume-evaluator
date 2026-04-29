<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});
Route::get('/login', function () {
    return view('auth.login');
});
Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/dashboard/groups', function () {
    return view('dashboard.groups.index');
});
Route::get('/dashboard/groups/{id}', function ($id) {
    $groups = [
        [
            'id' => 1,
            'name' => 'Senior Seminar W25',
            'status' => 'active',
            'pending_assignments' => 1,
            'resumes' => [
                [
                    'id' => 101,
                    'name' => 'Resume 1',
                    'ats_friendliness' => 90,
                    'keyword_match' => 67,
                ],
                [
                    'id' => 102,
                    'name' => 'Resume 2',
                    'ats_friendliness' => 82,
                    'keyword_match' => 71,
                ],
            ],
            'job_listings' => [
                [
                    'id' => 201,
                    'name' => 'Junior Backend Developer - CivicStack',
                    'description' => 'Build and maintain Laravel APIs for civic engagement tools. '
                        .'Looking for strong SQL fundamentals, clear Git workflow habits, '
                        .'and experience shipping class or internship projects.',
                ],
                [
                    'id' => 202,
                    'name' => 'Software Engineering Intern - BrightPath Health',
                    'description' => 'Support internal dashboard features with PHP and JavaScript. '
                        .'Interns collaborate with designers, write tests, and present sprint '
                        .'demos to product and engineering mentors.',
                ],
            ],
            'assignments' => [
                [
                    'id' => 301,
                    'title' => 'Tailor Resume for CivicStack Backend Role',
                    'status' => 'pending',
                    'due_date' => '2026-04-30',
                    'resume_id' => 101,
                    'job_listing_id' => 201,
                ],
                [
                    'id' => 302,
                    'title' => 'Internship Submission - BrightPath',
                    'status' => 'completed',
                    'due_date' => '2026-04-18',
                    'resume_id' => 102,
                    'job_listing_id' => 202,
                ],
            ],
        ],
        [
            'id' => 2,
            'name' => 'Senior Seminar W24',
            'status' => 'completed',
            'pending_assignments' => 0,
            'resumes' => [
                [
                    'id' => 103,
                    'name' => 'Resume 1',
                    'ats_friendliness' => 90,
                    'keyword_match' => 50,
                ],
                [
                    'id' => 104,
                    'name' => 'Resume 2',
                    'ats_friendliness' => 76,
                    'keyword_match' => 63,
                ],
            ],
            'job_listings' => [
                [
                    'id' => 203,
                    'name' => 'Platform Engineer - North River Logistics',
                    'description' => 'Own backend services that power shipment tracking and route planning. '
                        .'Ideal candidates have experience with distributed systems coursework, '
                        .'API performance tuning, and cloud deployment basics.',
                ],
                [
                    'id' => 204,
                    'name' => 'Frontend Engineer - BlueWave Analytics',
                    'description' => 'Develop React interfaces for analytics dashboards and partner closely '
                        .'with data teams. Role emphasizes accessibility, component architecture, '
                        .'and translating business metrics into clean visual experiences.',
                ],
            ],
            'assignments' => [
                [
                    'id' => 303,
                    'title' => 'Final Submission - North River Platform',
                    'status' => 'pending',
                    'due_date' => '2026-03-22',
                    'resume_id' => 103,
                    'job_listing_id' => 203,
                ],
                [
                    'id' => 304,
                    'title' => 'Portfolio Review - BlueWave Frontend',
                    'status' => 'completed',
                    'due_date' => '2026-03-29',
                    'resume_id' => 104,
                    'job_listing_id' => 204,
                ],
            ],
        ],
        [
            'id' => 3,
            'name' => 'Yeeh',
            'status' => 'active',
            'pending_assignments' => 0,
            'resumes' => [],
            'job_listings' => [
                [
                    'id' => 205,
                    'name' => 'QA Automation Engineer - Harbor Fintech',
                    'description' => 'Design automated test plans for payment workflows and customer onboarding. '
                        .'Experience with API testing, debugging CI failures, and writing '
                        .'maintainable test suites is a strong plus.',
                ],
                [
                    'id' => 206,
                    'name' => 'IT Business Analyst Intern - Metro Public Services',
                    'description' => 'Work with stakeholders to document requirements and map current processes. '
                        .'Great fit for students who can communicate technical ideas clearly '
                        .'and turn feedback into actionable tickets.',
                ],
            ],
            'assignments' => [],
        ],
    ];
    $group = collect($groups)->firstWhere('id', $id);

    return view('dashboard.groups.show', [
        'group_name' => $group['name'],
        'status' => $group['status'],
        'pending_assignment' => $group['pending_assignments'],
        'assignments' => $group['assignments'],
        'job_listings' => $group['job_listings'],
    ]);
}
)->name('dashboard.groups.show');

Route::get('/dashboard/groups/{id}/assignments/create', function ($id) {
    $groups = [
        [
            'id' => 1,
            'name' => 'Senior Seminar W25',
            'status' => 'active',
            'pending_assignments' => 1,
            'resumes' => [
                [
                    'id' => 101,
                    'name' => 'Resume 1',
                    'ats_friendliness' => 90,
                    'keyword_match' => 67,
                ],
                [
                    'id' => 102,
                    'name' => 'Resume 2',
                    'ats_friendliness' => 82,
                    'keyword_match' => 71,
                ],
            ],
            'job_listings' => [
                [
                    'id' => 201,
                    'name' => 'Junior Backend Developer - CivicStack',
                    'description' => 'Build and maintain Laravel APIs for civic engagement tools. '
                        .'Looking for strong SQL fundamentals, clear Git workflow habits, '
                        .'and experience shipping class or internship projects.',
                ],
                [
                    'id' => 202,
                    'name' => 'Software Engineering Intern - BrightPath Health',
                    'description' => 'Support internal dashboard features with PHP and JavaScript. '
                        .'Interns collaborate with designers, write tests, and present sprint '
                        .'demos to product and engineering mentors.',
                ],
            ],
            'assignments' => [
                [
                    'id' => 301,
                    'title' => 'Tailor Resume for CivicStack Backend Role',
                    'status' => 'pending',
                    'due_date' => '2026-04-30',
                    'resume_id' => 101,
                    'job_listing_id' => 201,
                ],
                [
                    'id' => 302,
                    'title' => 'Internship Submission - BrightPath',
                    'status' => 'completed',
                    'due_date' => '2026-04-18',
                    'resume_id' => 102,
                    'job_listing_id' => 202,
                ],
            ],
        ],
        [
            'id' => 2,
            'name' => 'Senior Seminar W24',
            'status' => 'completed',
            'pending_assignments' => 0,
            'resumes' => [
                [
                    'id' => 103,
                    'name' => 'Resume 1',
                    'ats_friendliness' => 90,
                    'keyword_match' => 50,
                ],
                [
                    'id' => 104,
                    'name' => 'Resume 2',
                    'ats_friendliness' => 76,
                    'keyword_match' => 63,
                ],
            ],
            'job_listings' => [
                [
                    'id' => 203,
                    'name' => 'Platform Engineer - North River Logistics',
                    'description' => 'Own backend services that power shipment tracking and route planning. '
                        .'Ideal candidates have experience with distributed systems coursework, '
                        .'API performance tuning, and cloud deployment basics.',
                ],
                [
                    'id' => 204,
                    'name' => 'Frontend Engineer - BlueWave Analytics',
                    'description' => 'Develop React interfaces for analytics dashboards and partner closely '
                        .'with data teams. Role emphasizes accessibility, component architecture, '
                        .'and translating business metrics into clean visual experiences.',
                ],
            ],
            'assignments' => [
                [
                    'id' => 303,
                    'title' => 'Final Submission - North River Platform',
                    'status' => 'pending',
                    'due_date' => '2026-03-22',
                    'resume_id' => 103,
                    'job_listing_id' => 203,
                ],
                [
                    'id' => 304,
                    'title' => 'Portfolio Review - BlueWave Frontend',
                    'status' => 'completed',
                    'due_date' => '2026-03-29',
                    'resume_id' => 104,
                    'job_listing_id' => 204,
                ],
            ],
        ],
        [
            'id' => 3,
            'name' => 'Yeeh',
            'status' => 'active',
            'pending_assignments' => 0,
            'resumes' => [],
            'job_listings' => [
                [
                    'id' => 205,
                    'name' => 'QA Automation Engineer - Harbor Fintech',
                    'description' => 'Design automated test plans for payment workflows and customer onboarding. '
                        .'Experience with API testing, debugging CI failures, and writing '
                        .'maintainable test suites is a strong plus.',
                ],
                [
                    'id' => 206,
                    'name' => 'IT Business Analyst Intern - Metro Public Services',
                    'description' => 'Work with stakeholders to document requirements and map current processes. '
                        .'Great fit for students who can communicate technical ideas clearly '
                        .'and turn feedback into actionable tickets.',
                ],
            ],
            'assignments' => [],
        ],
    ];

    $group = collect($groups)->firstWhere('id', (int) $id);

    return view('dashboard.groups.assignment-create', [
        'group' => $group,
        'group_name' => $group['name'],
        'job_listings' => $group['job_listings'],
    ]);
})->name('dashboard.groups.assignments.create');

Route::get('/dashboard/resumes', function () {
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'groups' => [
                ['id' => 1, 'name' => 'Group 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'groups' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'groups' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];

    return view('dashboard.resumes.index', [
        'evaluations' => $evaluations,
    ]);
});
Route::get('/dashboard/resumes/{id}', function ($id) {
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'groups' => [
                ['id' => 1, 'name' => 'Group 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'groups' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'groups' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];
    $evaluation = collect($evaluations)->firstWhere('id', $id);

    return view('dashboard.resumes.show', [
        'title' => $evaluation['name'],
        'evaluation' => $evaluation['ats_friendliness'],
        'keyword_match' => $evaluation['keyword_match'],
        'groups' => $evaluation['groups'],
    ]);
})->name('dashboard.resumes.show');

// Route::get('/contact', function() {
//     return view('contact');
// });
