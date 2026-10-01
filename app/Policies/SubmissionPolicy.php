<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * The submitting student and the module's instructors (or global admins) see the full submission.
     */
    public function view(User $user, Submission $submission): bool
    {
        return $submission->user_id === $user->id
            || $user->isGlobalAdmin()
            || $user->isInstructorInModule($submission->assignment->module);
    }
}
