<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class submission extends Model
{
    /** @use HasFactory<\Database\Factories\SubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assignment_id',
        'assignment_version',
        'resubmission_count',
        'due_date_snapshot',
    ];

    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class);
    }
    // public function submission(): HasOne
    // {
    //     return $this->hasOne(Submission::class);
    // }
}
