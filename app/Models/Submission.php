<?php

namespace App\Models;

use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assignment_id',
        'assignment_version',
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
