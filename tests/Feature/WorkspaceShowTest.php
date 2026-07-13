<?php

use App\Models\Evaluation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the five most recent evaluations for a workspace', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->user($user->id)->create();

    $oldestSummary = 'Oldest evaluation summary that should not appear';
    $newestSummaries = [
        'Newest evaluation summary one',
        'Newest evaluation summary two',
        'Newest evaluation summary three',
        'Newest evaluation summary four',
        'Newest evaluation summary five',
    ];

    Evaluation::factory()->create([
        'workspace_id' => $workspace->id,
        'created_at' => now()->subDays(6),
        'evaluation_data' => [
            'enrichment' => ['analysis_summary' => $oldestSummary],
            'matched_keywords' => [],
            'missing_keywords' => [],
            'ai_phrases' => [],
            'warnings' => [],
        ],
    ]);

    foreach ($newestSummaries as $index => $summary) {
        Evaluation::factory()->create([
            'workspace_id' => $workspace->id,
            'created_at' => now()->subDays(5 - $index),
            'evaluation_data' => [
                'enrichment' => ['analysis_summary' => $summary],
                'matched_keywords' => [],
                'missing_keywords' => [],
                'ai_phrases' => [],
                'warnings' => [],
            ],
        ]);
    }

    $this->actingAs($user)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertSuccessful()
        ->assertSee('Recent evaluations')
        ->assertSee($newestSummaries[0])
        ->assertSee($newestSummaries[4])
        ->assertDontSee($oldestSummary);
});

it('shows evaluation details inside a dropdown', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->user($user->id)->create();
    $summary = 'Dropdown evaluation analysis summary';

    Evaluation::factory()->create([
        'workspace_id' => $workspace->id,
        'evaluation_data' => [
            'enrichment' => ['analysis_summary' => $summary],
            'matched_keywords' => ['Python'],
            'missing_keywords' => [],
            'ai_phrases' => [],
            'warnings' => [],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertSuccessful()
        ->assertSee('collapse collapse-arrow', false)
        ->assertSee($summary)
        ->assertSee('Resume analysis');
});

it('shows an empty state when a workspace has no evaluations', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->user($user->id)->create();

    $this->actingAs($user)
        ->get(route('dashboard.workspaces.show', $workspace))
        ->assertSuccessful()
        ->assertSee('No evaluation run yet');
});
