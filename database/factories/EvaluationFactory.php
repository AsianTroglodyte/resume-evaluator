<?php

namespace Database\Factories;

use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
use App\Models\Submission;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'submission_id' => null,
            'resume_text' => fake()->paragraphs(3, true),
            'job_listing_id' => null,
            'job_description_text' => fake()->optional()->paragraphs(2, true),
            'status' => EvaluationStatus::Completed,
            'failure_reason' => null,
            'evaluation_data' => [
                'keyword_match' => fake()->randomFloat(1, 0, 100),
                'matched_keywords' => ['Python', 'Laravel'],
                'missing_keywords' => ['Docker'],
                'ai_phrases' => [],
                'enrichment' => [
                    'analysis_summary' => fake()->sentence(),
                    'items_to_enrich' => [],
                    'questions' => [],
                ],
                'warnings' => [],
            ],
            'evaluator_version' => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EvaluationStatus::Failed,
            'failure_reason' => 'Evaluation service could not complete the request.',
            'evaluation_data' => null,
        ]);
    }

    
    public function withStatus(EvaluationStatus $evaluationStatus): static
    {
        return $this->state(fn (array $attributes)=> [
            "status" => $evaluationStatus
        ]);
    }

    public function withWorkspace(int $workspace_id): static
    {
        return $this->state(fn (array $attributes) => [
            'workspace_id' => $workspace_id,
            'submission_id' => null
        ]);
    }

    public function withSubmission(?Submission $submission = null): static
    {
        if ($submission === null){ 
            return $this->state(fn (array $attribute) => [
                'workspace_id' => null,
                'submission_id' => Submission::factory()
            ]);
        }

        return $this->state(fn (array $attribute) => [
            'workspace_id' => null,
            'submission_id' => $submission->id
        ]);
    }
}
